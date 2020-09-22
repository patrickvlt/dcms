<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace App\Classes;

class Datatable
{
    public function __construct($query, $searchFields){
        $this->query = $query;
        $this->searchFields = $searchFields;
    }

    /**
     * Build conditional where clauses
     * @param $field
     * @param $value
     */

    public function filter($field, $value)
    {
        switch ($field) {
            default:
                $this->query->where($field, '=', $value);
        }
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

        // Sort columns
        if (isset($params['sort'])) {
            // orderBy(field,asc)
            $this->query->orderBy($params['sort']['field'],$params['sort']['sort']);
        }

        // Perform general search on remaining results
        $data = $this->query->toArray() ?? $this->query->get()->toArray();

        if (isset($params['query']['generalSearch'])){
            $search = $params['query']['generalSearch'];
            // Filter the results array from previous query
            $data = array_filter($data, function ($row) use ($search) {
                // Loop through searchable columns
                foreach($this->searchFields as $field){
                    // Check if field exists in this array
                    if (isset($row[$field])){
                        // Check if search value is found in this field
                        if (strpos(strtolower($row[$field]), strtolower($search)) !== false){
                            return $row;
                        }
                    } else if (count(explode('.',$field)) > 1){
                        $newRow = explode('.',$field)[0];
                        $newField = explode('.',$field)[1];
                        // Check if search value is found in this field
                        if (isset($row[$newRow][$newField]) && strpos(strtolower($row[$newRow][$newField]), strtolower($search)) !== false){
                            return $row;
                        }
                    }
                }
            });
            // Clear data if general search cant find anything
            // Or else the results wont be affected
            if (count($data) >! 0){
                $data = [];
            }
        }

        // Retrieve pagination parameters, to paginate the results and return a meta response
        $total = count($data);
        $perPage = $params['pagination']['perpage'] ?? 5;
        $page = $params['pagination']['page']-1 ?? 0;

        // Paginate the results
        $paginatedData = array_chunk($data, $perPage, true);
        $pages = count($paginatedData);

        // Make response object with meta
        $response = (object) '';
        $response->meta = [
            'page' => $params['pagination']['page'],
            'pages' => $pages,
            'perpage' => $perPage,
            'total' => $total,
        ];

        // Use the page parameter as an array index
        $response->data = $paginatedData[$params['pagination']['page']-1] ?? $paginatedData;

        return response()->json($response);
    }
}
