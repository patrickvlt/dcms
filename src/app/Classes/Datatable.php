<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

use Illuminate\Pagination\Paginator;

class Datatable
{
    public function __construct($query, $searchFields=null){
        $this->query = $query;
        $this->searchFields = $searchFields;
        $this->data = null;
    }

    /**
     * Build conditional where clauses
     * Override this function in custom datatable to use more clauses
     * @param $field
     * @param $value
     */

    public function filter($field=null, $value=null)
    {
        $this->data = array_filter($this->data, function($row) use ($field, $value) {
            return ($row[$field] == $value) ? $row : null;
        });
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

        // Get (per) page from Datatable query
        $perPage = isset($params['pagination']['perpage']) && $params['pagination']['perpage'] !== 'NaN' ? $params['pagination']['perpage'] : null;
        $page = isset($params['pagination']['page']) ? $params['pagination']['page'] : null;

        if ($perPage){
            // (re)paginate if user is on a page exceeding the max pages from this collection
            PaginateAgain:
            // Prepare pagination for query
            $paginator = $this->query->paginate($perPage,['*'],'page',$page);
            // Make collection from the paginated items
            $this->data = collect($paginator->items());
            // Get pages from paginator
            $pages = $paginator->lastPage();
            // If user is on a page outside of the paginators range
            if ($page > $pages){
                $page = $pages;
                goto PaginateAgain;
            }
            $total = $paginator->total();
        } else {
            $this->data = collect($this->query->get());
            $pages = 1;
            $page = 1;
        }

        // Sort all collected data first with Laravel's sortBy method
        if (isset($params['sort'])) {
            // orderBy(field,asc)
            $sortBy = ($params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
            // sort eager loaded arrays
            if (count(explode('.',$params['sort']['field'])) > 1){
                $sortField = '';
                foreach (explode('.',$params['sort']['field']) as $field){
                    $sortField .= "['".$field."']";
                }
                $sortField = eval("return ".$sortField." ?? null;");
            } else {
                $sortField = $params['sort']['field'];
            }
            $this->data = $this->data->{$sortBy}($params['sort']['field']);
        }

        // Convert collection to array
        $this->data = $this->data->toArray();
        sort($this->data);

        $filteredResults = false;
        $searchedResults = false;

        // Filter through array
        if (isset($params['query'])) {
            foreach ($params['query'] as $key => $value) {
                if ($key !== 'generalSearch'){
                    $this->filter($key,$value);
                }
            }
            $filteredResults = true;
        }
        
        // Perform general search on remaining results
        if (isset($params['query']['generalSearch']) && isset($this->data[0])){
            $searchValue = $params['query']['generalSearch'];
            $searchColumns = [];
            // Loop through searchable columns
            if (isset($this->searchFields)){
                $searchColumns = $this->searchFields;
            } else {
                foreach ($this->data[0] as $key => $val){
                    $searchColumns[] = $key;
                }
            }
            $newData = [];
            $addedRows = [];
            // Filter the results array from previous query
            // First, flatten the array so no nested values remain
            // Then preg match the array keys with the search field
            // Then preg match the search value with the matched row in the array
            foreach (Flatten($this->data) as $flatKey => $flatValue){
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
                            $newData[] = $this->data[$dataRow];
                            $addedRows[] = $dataRow;
                        }
                    }
                }
            }

            // Clear data if general search cant find anything
            // Or else the results wont be affected
            $this->data = (count($this->data) >! 0) ? [] : $newData;
            $searchedResults = true;
        }

        if ($filteredResults || $searchedResults){
            // Check if total and pages are still the same
            if ($total !== count($this->data)){
                $total = count($this->data);
            }

            // Check if per page and total are different
            if ($total < $perPage){
                $pages = 1;
            }
        }

        // Make response object with meta
        $response = (object) '';
        
        // Paginate the results if page and perpage parameters are present
        if (isset($page,$perPage) && $total > 0){
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
