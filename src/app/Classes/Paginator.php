<?php

namespace Pveltrop\DCMS\Classes;

class Paginator
{
    public static function generate($data){
        // Get parameters from request
        $params = request()->all();

        // Get (per) page from
        $perPage = isset($params['pagination']['perpage']) && $params['pagination']['perpage'] !== 'NaN' ? $params['pagination']['perpage'] : null;
        $page = isset($params['pagination']['page']) ? $params['pagination']['page'] : null;

        // Generate collection
        $data = collect($data);

        // Sort the collection, nested columns will work too
        if (isset($params['sort'])) {
            $sortBy = ($params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
            $data = $data->{$sortBy}($params['sort']['field']);
        }

        // Paginate the collection instead of query
        $total = count($data);
        if ($perPage){
            Paginate:
            $pages = (int)ceil($total / $perPage);
            // If user is outside the pages range when changing pagination preferences
            if ($page > $pages) {
                // Set page to max possible page
                $page = $pages;
                goto Paginate;
            }
            $data = $data->forPage($page,$perPage);
        } else {
            $pages = 1;
            $page = 1;
        }

        // Make response object with meta
        $response = (object) 'query';

        // Paginate the results if page and perpage parameters are present
        if (isset($page, $perPage) && $total > 0) {
            $response->data = $data;
            $response->meta = [
                'page' => $page,
                'pages' => $pages,
                'perpage' => $perPage,
                'total' => $total,
                'sort' => $params['sort']['sort'] ?? null,
                'field' => $params['sort']['field'] ?? null,
            ];
        } else {
            // Return all data if no pagination parameters are present
            $response->data = $data;
        }

        return response()->json($response);
    }
}
