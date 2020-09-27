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

        // Query may already have been executed dynamically
        try {
            $data = $this->query->toArray();
        } catch (\Exception $e) {
            // If query hasnt been executed yet
            $data = $this->query->get()->toArray();
        }

        // Perform general search on remaining results
        if (isset($params['query']['generalSearch'])){
            $search = $params['query']['generalSearch'];
            // Filter the results array from previous query
            $data = array_filter($data, function ($row) use ($search, $data) {
                // Loop through searchable columns
                if (isset($this->searchFields)){
                    $searchColumns = $this->searchFields;
                } else {
                    foreach ($data[0] as $key => $val){
                        $searchColumns[] = $key;
                    }
                }
                foreach($searchColumns as $key => $field){;
                    // Check if field exists in this array
                    if (isset($row[$field])){
                        // Check if search value is found in this field
                        // Skip if value is an array, most likely a relation/FK
                        // To search in relations, define a column to another table, e.g.: posts.user_id
                        if (!is_array($row[$field]) && strpos(strtolower($row[$field]), strtolower($search)) !== false){
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
        $perPage = isset($params['pagination']['perpage']) ? $params['pagination']['perpage'] : null;
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
            ];
        } else {
            // Return all data if no pagination parameters are present
            $response->data = $data;
        }

        return response()->json($response);
    }
}
