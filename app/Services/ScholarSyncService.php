<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Madhhab;
use App\Models\Scholar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScholarSyncService
{
    public function __construct(protected CalComService $cal)
    {
    }

    /**
     * @return array{created: int, updated: int, total: int}
     */
    public function sync(): array
    {
        Cache::forget('cal.team.scholars.v3');
        $payload = $this->cal->scholars();
        $madhahib = Madhhab::query()->get();
        $languages = Language::query()->get();

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($payload, $madhahib, $languages, &$created, &$updated) {
            foreach ($payload as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $username = $row['username'] ?? $row['id'] ?? null;
                $email = $this->normalizeEmail($row['email'] ?? null);

                if ((! is_string($username) || $username === '') && $email === null) {
                    continue;
                }

                $scholar = $this->findScholar($row) ?? new Scholar;
                $exists = $scholar->exists;
                $matchedMadhhabId = $this->matchMadhhab($madhahib, $row['madhhab'] ?? null);
                $raw = is_array($row['raw'] ?? null) ? $row['raw'] : $row;

                $scholar->fill([
                    'cal_membership_id' => $row['membershipId'] ?? $scholar->cal_membership_id,
                    'cal_username' => is_string($username) && $username !== '' ? $username : $scholar->cal_username,
                    'cal_user_id' => $row['userId'] ?? $scholar->cal_user_id,
                    'email' => $email ?? $scholar->email,
                    'name' => $row['name'] ?? $scholar->name ?? ($username ?: $email),
                    'initials' => $row['initials'] ?? $scholar->initials,
                    'avatar_url' => $row['avatarUrl'] ?? $scholar->avatar_url,
                    'bio' => $row['bio'] ?? $scholar->bio,
                    'credentials' => $row['credentials'] ?? $scholar->credentials,
                    'country' => $row['country'] ?? $scholar->country,
                    'flag' => $row['flag'] ?? $scholar->flag,
                    'tier' => $row['tier'] ?? $scholar->tier ?? 'standard',
                    'specialties' => $row['specialties'] ?? $scholar->specialties,
                    'schedule' => $row['schedule'] ?? $scholar->schedule,
                    'last_synced_at' => now(),
                    'raw_payload' => $raw,
                ]);

                if ($matchedMadhhabId) {
                    $scholar->madhhab_id = $matchedMadhhabId;
                } elseif (! $exists) {
                    $scholar->madhhab_id = null;
                }

                $scholar->save();

                $calLanguages = $row['languages'] ?? [];

                if (is_array($calLanguages) && $calLanguages !== []) {
                    $scholar->languages()->sync($this->matchLanguages($languages, $calLanguages));
                }

                $exists ? $updated++ : $created++;
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($payload),
        ];
    }

    /**
     * Match an existing scholar by Cal.com user id, membership id, email, or username.
     *
     * @param  array<string, mixed>  $row
     */
    protected function findScholar(array $row): ?Scholar
    {
        $userId = $row['userId'] ?? null;
        $membershipId = $row['membershipId'] ?? null;
        $email = $this->normalizeEmail($row['email'] ?? null);
        $username = $row['username'] ?? $row['id'] ?? null;

        if (! $userId && ! $membershipId && $email === null && (! is_string($username) || $username === '')) {
            return null;
        }

        return Scholar::query()
            ->where(function ($query) use ($userId, $membershipId, $email, $username) {
                if ($userId) {
                    $query->orWhere('cal_user_id', $userId);
                }

                if ($membershipId) {
                    $query->orWhere('cal_membership_id', $membershipId);
                }

                if ($email !== null) {
                    $query->orWhere('email', $email);
                }

                if (is_string($username) && $username !== '') {
                    $query->orWhere('cal_username', $username);
                }
            })
            ->first();
    }

    protected function normalizeEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $email = mb_strtolower(trim($email));

        return $email === '' ? null : $email;
    }

    protected function matchMadhhab(Collection $madhahib, mixed $name): ?int
    {
        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        $needle = mb_strtolower(trim($name));

        $match = $madhahib->first(function (Madhhab $madhhab) use ($needle) {
            return mb_strtolower($madhhab->name) === $needle
                || mb_strtolower($madhhab->slug) === $needle;
        });

        return $match?->id;
    }

    /**
     * @param  list<mixed>  $names
     * @return list<int>
     */
    protected function matchLanguages(Collection $languages, array $names): array
    {
        $needles = collect($names)
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->map(fn ($name) => mb_strtolower(trim($name)));

        return $languages
            ->filter(function (Language $language) use ($needles) {
                return $needles->contains(mb_strtolower($language->name))
                    || $needles->contains(mb_strtolower($language->code));
            })
            ->pluck('id')
            ->all();
    }
}
