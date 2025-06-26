<?php

namespace App\Traits;

trait PaginationTrait
{
    /**
     * Handle pagination for query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function handlePagination($query, $request, $columns = ['*'])
    {
        $page = $request->input('page');
        $limit = $request->input('limit');

        if (empty($page) && empty($limit)) {
            return $query->get($columns);
        }

        return $query->paginate($limit ?? 10, $columns);
    }

    /**
     * Format pagination response with only necessary data
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return array
     */
    protected function formatPaginationResponse($paginator)
    {
        return [
            'data' => $paginator->items(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'lastPage' => $paginator->lastPage(),
            ]
        ];
    }

    /**
     * Handle pagination and format response
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param array $columns
     * @return array
     */
    protected function handlePaginationWithFormat($query, $request, $columns = ['*'])
    {
        $result = $this->handlePagination($query, $request, $columns);

        if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return $this->formatPaginationResponse($result);
        }

        return ['data' => $result];
    }
}
