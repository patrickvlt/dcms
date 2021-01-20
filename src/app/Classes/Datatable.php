<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

use Illuminate\Support\Collection;

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

    public function buildFilters()
    {
        foreach ($this->params['query'] as $key => $value) {
            if ($key !== 'generalSearch') {
                if ($this->queryBuilder) {
                    $this->filter($key, $value);
                } else {
                    $this->query = $this->filter($key, $value);
                }
            }
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
        $this->params = request()->all();

        // Check if a query builder has been passed, or an array/collection
        $this->queryBuilder = (!$this->query instanceof Collection && !is_array($this->query)) ? true : false;

        // Build filters for query
        if (isset($this->params['query'])) {
            if ($this->queryBuilder) {
                $this->query->where(function ($q) {
                    $this->buildFilters();
                });
            } else {
                $this->buildFilters();
            }
        }


        // Get (per) page from Datatable query
        $perPage = isset($this->params['pagination']['perpage']) && $this->params['pagination']['perpage'] !== 'NaN' ? $this->params['pagination']['perpage'] : null;
        $page = isset($this->params['pagination']['page']) ? $this->params['pagination']['page'] : null;

        // General search
        if (isset($this->params['query']['generalSearch'])) {

            // Get first row from query to grab the keys/fields
            $this->firstRow = ($this->query) ? $this->query->first() : null;

            // If there's no data already, skip this step
            if (!$this->firstRow) {
                goto GenerateData;
            }

            $this->firstRowArr = is_array($this->firstRow) ? $this->firstRow : $this->firstRow->toArray();
            $this->searchValue = strtolower($this->params['query']['generalSearch']);

            // Dynamically make where(has) clauses for generalsearch, if a query builder has been passed
            if($this->queryBuilder){
                $this->query->where(function ($q) {
                    foreach ($this->firstRowArr as $this->entry => $value) {  
                        // If column name isnt in the models attributes, so its a relation
                        // Dynamically make wherehas clauses for generalsearch
                        if (!in_array($this->entry,array_keys($this->firstRow->getAttributes()))) {
                            $q->whereHas($this->entry, function ($q) {
                                $y = 0;
                                foreach ($this->firstRow->{$this->entry}->toArray() as $relatedEntry => $relatedValue) {
                                    if(!is_array($relatedValue)){
                                        if ($y == 0){
                                            $q->where($relatedEntry,'LIKE','%'.strtolower($this->searchValue).'%');
                                        } 
                                        else {
                                            $q->orWhere($relatedEntry,'LIKE','%'.strtolower($this->searchValue).'%');
                                        }
                                    }
                                    $y++;
                                }
                            });
                        }
                    }
                    foreach ($this->firstRowArr as $entry => $value) {  
                        // Dynamically make where clauses for generalsearch
                        if(!is_array($value)){
                            $q->orWhere($entry,'LIKE','%'.strtolower($this->searchValue).'%');
                        }
                    }
                });
            } else {
                $fetchData = $this->query;
                $this->query = [];
                foreach($fetchData as $dataKey => $dataRow){
                    $searchRe = '/\:(\"|)'.strtolower($this->searchValue).'.*?(\,)/m';
                    $searchIn = strtolower(json_encode($dataRow));
                    if (preg_match($searchRe,$searchIn) > 0){
                        $this->query[] = $dataRow;
                    }
                }
            }
        }

        // Generate collection from results
        GenerateData:

        $this->data = [];
        if ($this->queryBuilder) {
            $this->data = collect($this->query->get());
        } else if ($this->query) {
            $this->data = collect($this->query);
        }

        $total = 0;
        if ($this->data) {
            // Sort the collection, nested columns will work too
            if (isset($this->params['sort'])) {
                $sortBy = ($this->params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
                $this->data = $this->data->{$sortBy}($this->params['sort']['field'])->values();
            }
            
            // Paginate the collection
            $total = count($this->data);
            if ($perPage) {
                // Calculate how many pages are available by diving the total amount of data by perpage, then rounding up
                $pages = (int)ceil($total / $perPage);
                // If user is outside the pages range when changing pagination preferences
                // Set page to max possible page
                if ($page > $pages) {
                    $page = $pages;
                }
                $this->data = $this->data->forPage($page, $perPage);
            } else {
                $pages = 1;
                $page = 1;
            }
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
                'sort' => $this->params['sort']['sort'] ?? null,
                'field' => $this->params['sort']['field'] ?? null,
            ];
        } else {
            // Return all data if no pagination parameters are present
            $response->data = $this->data;
        }

        return response()->json($response);
    }
}
