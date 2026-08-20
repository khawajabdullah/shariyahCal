<?php

namespace App\Support\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DataTableQuery
{
    /**
     * @param  array<int, string>  $searchable
     * @param  array<string, string>  $sortable  request key => column
     */
    public function __construct(
        protected Builder $query,
        protected array $searchable,
        protected array $sortable,
        protected Request $request,
    ) {
    }

    /**
     * @param  callable(Collection): mixed  $transform
     * @return array<string, mixed>
     */
    public function toResponse(?callable $transform = null): array
    {
        $draw = (int) $this->request->input('draw', 1);
        $length = (int) $this->request->input('length', $this->request->input('per_page', 10));
        $start = (int) $this->request->input('start', 0);

        if ($this->request->filled('page') && ! $this->request->filled('start')) {
            $page = max(1, (int) $this->request->input('page', 1));
            $start = ($page - 1) * max(1, $length);
        }

        $length = $length < 1 ? 10 : min($length, 100);

        $recordsTotal = (clone $this->query)->count();

        $search = $this->searchValue();

        if ($search !== '') {
            $this->query->where(function (Builder $query) use ($search) {
                foreach ($this->searchable as $index => $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $field] = explode('.', $column, 2);
                        $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                        $query->{$method}($relation, function (Builder $relationQuery) use ($field, $search) {
                            $relationQuery->where($field, 'like', '%'.$search.'%');
                        });

                        continue;
                    }

                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', '%'.$search.'%');
                }
            });
        }

        $recordsFiltered = (clone $this->query)->count();

        [$sortColumn, $sortDir] = $this->sort();
        $this->query->orderBy($sortColumn, $sortDir)->orderByDesc($this->query->getModel()->getQualifiedKeyName());

        $rows = $this->query->skip($start)->take($length)->get();
        $data = $transform ? $transform($rows) : $rows;

        $page = (int) floor($start / $length) + 1;
        $lastPage = max(1, (int) ceil($recordsFiltered / $length));

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $length,
                'from' => $recordsFiltered === 0 ? 0 : $start + 1,
                'to' => min($start + $length, $recordsFiltered),
            ],
        ];
    }

    protected function searchValue(): string
    {
        $search = $this->request->input('search.value', $this->request->input('search', ''));

        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }

        return trim((string) $search);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function sort(): array
    {
        $dir = strtolower((string) $this->request->input('order.0.dir', $this->request->input('dir', 'asc')));
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';

        $requested = $this->request->input('sort');

        if (! $requested) {
            $columnIndex = $this->request->input('order.0.column');
            $requested = $this->request->input("columns.{$columnIndex}.data")
                ?? $this->request->input("columns.{$columnIndex}.name");
        }

        $requested = (string) $requested;
        $column = $this->sortable[$requested] ?? reset($this->sortable) ?: 'id';

        return [$column, $dir];
    }
}
