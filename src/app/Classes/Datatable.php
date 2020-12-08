<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

class Datatable
{
    public function __construct($query, $searchFields = null, $excludeSearchFields = [])
    {
        $this->query = $query;
        $this->searchFields = $searchFields;
        $this->excludeSearchFields = $excludeSearchFields;
        $this->data = null;
    }

    /**
     * Build conditional where clauses
     * Override this function in custom datatable to use more clauses
     * @param $field
     * @param $value
     */

    public function filter($field = null, $value = null)
    {
        $this->query->where($field, '=', $value);
    }

    /**
     * Build query based on parameters in user request
     * Paginate the result with array_chunk
     * Return response with data and meta
     * @return \Illuminate\Http\JsonResponse
     */

    public function render(): \Illuminate\Http\JsonResponse
    {
        // Get parameters from request
        $params = request()->all();

        // Build filters for query
        if (isset($params['query'])) {
            foreach ($params['query'] as $key => $value) {
                if ($key !== 'generalSearch') {
                    $this->filter($key, $value);
                }
            }
        }

        // Get (per) page from Datatable query
        $perPage = isset($params['pagination']['perpage']) && $params['pagination']['perpage'] !== 'NaN' ? $params['pagination']['perpage'] : null;
        $page = isset($params['pagination']['page']) ? $params['pagination']['page'] : null;

        // Generate collection from results
        $this->data = collect($this->query->get());

        // Sort the collection, nested columns will work too
        if (isset($params['sort'])) {
            $sortBy = ($params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
            $this->data = $this->data->{$sortBy}($params['sort']['field']);
        }

        // Perform general search on remaining results
        if (isset($params['query']['generalSearch']) && isset($this->data[0])){
            $searchValue = $params['query']['generalSearch'];
            $searchColumns = [];

            // If no searchable columns are passed, use all columns
            if (isset($this->searchFields)){
                $searchColumns = $this->searchFields;
            } else {
                foreach ($this->data[0] as $key => $val){
                    $searchColumns[] = $key;
                }
            }

            // Make new array with foreach, this is faster than using array_filter
            $newData = [];
            foreach($searchColumns as $searchColumn){
                foreach($this->data as $dataKey => $dataRow){
                    $searchRe = '/\:(\"|)'.strtolower($searchValue).'.*?(\,)/m';
                    $searchIn = strtolower(json_encode($dataRow));
                    if (!in_array($searchColumn,$this->excludeSearchFields)){
                        if (preg_match($searchRe,$searchIn) > 0){
                            $newData[] = $dataRow;
                        }
                    }
                }
            }

            // Clear data if general search cant find anything
            // Or else the results wont be affected
            $this->data = (count($newData) >! 0) ? [] : $newData;
            $this->data = collect(array_unique($this->data,SORT_REGULAR));
            $total = count($this->data);
        }

        // Paginate the collection instead of query
        $total = count($this->data);
        if ($perPage){
            Paginate:
            $pages = (int)ceil($total / $perPage);
            // If user is outside the pages range when changing pagination preferences
            if ($page > $pages) {
                // Set page to max possible page
                $page = $pages;
                goto Paginate;
            }
            $this->data = $this->data->forPage($page,$perPage);
        } else {
            $pages = 1;
            $page = 1;
        }

        // Make response object with meta
        $response = (object) 'query';

        // Paginate the results if page and perpage parameters are present
        if (isset($page, $perPage) && $total > 0) {
            $response->data = $this->data;
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
            $response->data = $this->data;
        }

        return response()->json($response);
    }
}
