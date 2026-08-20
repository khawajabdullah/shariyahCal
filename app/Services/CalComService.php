<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CalComService
{
    public function scholars(): array
    {
        $ttl = (int) config('services.cal.cache_ttl', 300);

        return Cache::remember('cal.team.scholars.v3', $ttl, function () {
            return $this->fetchScholars();
        });
    }

    public function scholar(string $id): ?array
    {
        foreach ($this->scholars() as $scholar) {
            if (($scholar['id'] ?? null) === $id) {
                return $scholar;
            }
        }

        return null;
    }

    public function bookings(int $take = 250): array
    {
        $key = (string) config('services.cal.key');
        $base = rtrim((string) config('services.cal.base_url'), '/');

        if ($key === '') {
            throw new RuntimeException('Cal.com API credentials are not configured.');
        }

        $response = Http::timeout(25)
            ->acceptJson()
            ->withHeaders(['Authorization' => $key])
            ->get("{$base}/bookings", ['take' => $take]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Cal.com bookings request failed: '.$response->status().' '.$response->body()
            );
        }

        $data = $response->json('data') ?? [];

        return is_array($data) ? $data : [];
    }

    protected function fetchScholars(): array
    {
        $key = (string) config('services.cal.key');
        $teamId = (string) config('services.cal.team_id');
        $base = rtrim((string) config('services.cal.base_url'), '/');

        if ($key === '' || $teamId === '') {
            throw new RuntimeException('Cal.com API credentials are not configured.');
        }

        $headers = ['Authorization' => $key];

        $responses = Http::pool(fn (Pool $pool) => [
            $pool->as('members')->timeout(20)->acceptJson()->withHeaders($headers)
                ->get("{$base}/teams/{$teamId}/memberships", ['take' => 250]),
            $pool->as('schedules')->timeout(20)->acceptJson()->withHeaders($headers)
                ->get("{$base}/teams/{$teamId}/schedules", ['take' => 250]),
        ]);

        if ($responses['members']->failed()) {
            throw new RuntimeException(
                'Cal.com memberships request failed: '.$responses['members']->status().' '.$responses['members']->body()
            );
        }

        $schedulesByOwner = $this->schedulesByOwner($responses['schedules']);
        $members = $responses['members']->json('data') ?? [];

        return collect($members)
            ->map(function (array $membership) use ($schedulesByOwner) {
                $scholar = $this->mapMembership($membership);

                if (! $scholar) {
                    return null;
                }

                $ownerSchedules = $schedulesByOwner
                    ->get((string) ($scholar['userId'] ?? ''), collect())
                    ->values()
                    ->all();

                $scholar['schedule'] = $this->pickSchedule($ownerSchedules);

                return $scholar;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    protected function schedulesByOwner(mixed $response): Collection
    {
        if (! is_object($response) || ! method_exists($response, 'successful') || ! $response->successful()) {
            if (is_object($response) && method_exists($response, 'status')) {
                report(new RuntimeException(
                    'Cal.com schedules request failed: '.$response->status().' '.$response->body()
                ));
            }

            return collect();
        }

        return collect($response->json('data') ?? [])
            ->map(fn (array $schedule) => $this->mapSchedule($schedule))
            ->filter(fn (array $schedule) => ! empty($schedule['isDefault']))
            ->groupBy(fn (array $schedule) => (string) ($schedule['ownerId'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $schedule
     * @return array<string, mixed>
     */
    protected function mapSchedule(array $schedule): array
    {
        return [
            'id' => $schedule['id'] ?? null,
            'ownerId' => isset($schedule['ownerId']) ? (int) $schedule['ownerId'] : null,
            'name' => $schedule['name'] ?? 'Working hours',
            'timeZone' => $schedule['timeZone'] ?? 'UTC',
            'isDefault' => (bool) ($schedule['isDefault'] ?? false),
            'availability' => $schedule['availability'] ?? [],
            'overrides' => $schedule['overrides'] ?? [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $schedules
     * @return array<string, mixed>|null
     */
    protected function pickSchedule(array $schedules): ?array
    {
        return collect($schedules)->first(fn (array $schedule) => ! empty($schedule['isDefault']));
    }

    protected function mapMembership(array $membership): ?array
    {
        if (empty($membership['accepted'])) {
            return null;
        }

        $user = $membership['user'] ?? [];
        $username = $user['username'] ?? null;

        if (! is_string($username) || $username === '') {
            return null;
        }

        $name = trim((string) ($user['name'] ?? $username));
        [$bio, $credentials] = $this->parseBio((string) ($user['bio'] ?? ''));
        $meta = is_array($user['metadata'] ?? null) ? $user['metadata'] : [];

        $email = $user['email'] ?? null;
        $email = is_string($email) ? mb_strtolower(trim($email)) : null;

        return [
            'id' => $username,
            'membershipId' => isset($membership['id']) ? (int) $membership['id'] : null,
            'userId' => isset($membership['userId']) ? (int) $membership['userId'] : null,
            'teamId' => isset($membership['teamId']) ? (int) $membership['teamId'] : null,
            'username' => $username,
            'email' => $email !== '' ? $email : null,
            'name' => $name,
            'initials' => $this->initials($name),
            'avatarUrl' => $user['avatarUrl'] ?? null,
            'bio' => $bio,
            'credentials' => $credentials,
            'role' => $membership['role'] ?? null,
            'country' => $this->stringMeta($meta, 'country'),
            'flag' => $this->stringMeta($meta, 'flag'),
            'madhhab' => $this->stringMeta($meta, 'madhhab'),
            'languages' => $this->listMeta($meta, 'languages'),
            'specialties' => $this->listMeta($meta, 'specialties'),
            'tier' => ($membership['role'] ?? '') === 'OWNER' ? 'institutional' : 'standard',
            'raw' => $membership,
        ];
    }

    /**
     * Split a Cal.com bio into a summary paragraph and credential bullets.
     *
     * @return array{0: string, 1: list<string>}
     */
    protected function parseBio(string $bio): array
    {
        $bio = trim($bio);

        if ($bio === '') {
            return ['', []];
        }

        $paragraphs = [];
        $credentials = [];

        foreach (preg_split('/\r\n|\r|\n/', $bio) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^[-•]\s*(.+)$/u', $trimmed, $matches)) {
                $credentials[] = $matches[1];
                continue;
            }

            $paragraphs[] = $trimmed;
        }

        return [implode("\n\n", $paragraphs), $credentials];
    }

    protected function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '';
        }

        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[array_key_last($parts)], 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    protected function stringMeta(array $meta, string $key): ?string
    {
        $value = $meta[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return list<string>
     */
    protected function listMeta(array $meta, string $key): array
    {
        $value = $meta[$key] ?? [];

        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->map(fn ($item) => trim($item))
            ->values()
            ->all();
    }
}
