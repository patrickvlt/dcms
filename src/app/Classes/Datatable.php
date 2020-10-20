<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

class Datatable
{
    public function __construct($query, $searchFields=null){
        $this->query = $query;
        $this->searchFields = $searchFields;
    }

    /**
     * Build conditional where clauses
     * Override this function in custom datatable to use more clauses
     * @param $field
     * @param $value
     */

    public function filter($field=[], $value=[])
    {
        return $this->query->where($field, '=', $value);
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

        // Execute query
        if (isset($params['query'])) {
            foreach ($params['query'] as $key => $value) {
                if ($key !== 'generalSearch'){
                    $this->filter($key,$value);
                }
            }
        }

        $data = $this->query->get();

        if (isset($params['sort'])) {
            // orderBy(field,asc)
            $sortBy = ($params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
            $data = $data->{$sortBy}($params['sort']['field']);
        }

        $data = array_values($data->toArray());

        // Perform general search on remaining results
        if (isset($params['query']['generalSearch'])){
            $searchValue = $params['query']['generalSearch'];
            $searchColumns = [];
            // Loop through searchable columns
            if (isset($this->searchFields)){
                $searchColumns = $this->searchFields;
            } else {
                foreach ($data[0] as $key => $val){
                    $searchColumns[] = $key;
                }
            }
            $newData = [];
            $addedRows = [];
            // Filter the results array from previous query
            // First, flatten the array so no nested values remain
            // Then preg match the array keys with the search field
            // Then preg match the search value with the matched row in the array
            foreach (Flatten($data) as $flatKey => $flatValue){
                $dataRow = explode('.',$flatKey)[0];
                foreach($searchColumns as $x => $searchField){
                    // Make new search field by exploding . and grabbing the last element
                    if (count(explode('.',$searchField)) > 1){
                        $explodedFields = explode('.',$searchField);
                        $searchField = end($explodedFields);
                    }
                    // Check if search key matches any key in the data
                    if (preg_match('/'.strtolower($searchField).'/m', strtolower($flatKey)) > 0){
                        // Check if search value is found, then push to new array
                        if (!in_array($dataRow,$addedRows) && $flatValue !== null && $flatValue !== '' && preg_match('/'.strtolower($searchValue).'/m', strtolower($flatValue)) > 0){
                            $newData[] = $data[$dataRow];
                            $addedRows[] = $dataRow;
                        }
                    }
                }
            }

            // Clear data if general search cant find anything
            // Or else the results wont be affected
            $data = (count($data) >! 0) ? [] : $newData;
        }

        // Retrieve pagination parameters, to paginate the results and return a meta response
        $total = count($data);
        $perPage = isset($params['pagination']['perpage']) && $params['pagination']['perpage'] !== 'NaN' ? $params['pagination']['perpage'] : null;
        $page = isset($params['pagination']['page']) ? $params['pagination']['page']-1 : null;

        // Make response object with meta
        $response = (object) '';
        // Paginate the results if page and perpage parameters are present
        if (isset($page,$perPage) && count($data) > 0){
            $paginatedData = array_chunk($data, $perPage, true);
            $pages = count($paginatedData);
            // If page/key exists in paginated data array, return this data
            if (isset($paginatedData[$page])){
                $response->data = $paginatedData[$page];
                // If it doesnt exist, set the meta page to 1 and array key to 0
            } else {
                $response->data = $paginatedData[0];
                $page = 0;
            }
            $response->meta = [
                'page' => $page+1,
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
