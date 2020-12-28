<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

use Illuminate\Database\Eloquent\Collection;

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
            $this->query->where(function ($q) use ($params){
                foreach ($params['query'] as $key => $value) {
                    if ($key !== 'generalSearch') {
                        $this->filter($key, $value);
                    }
                }
            });
        }

        // Get (per) page from Datatable query
        $perPage = isset($params['pagination']['perpage']) && $params['pagination']['perpage'] !== 'NaN' ? $params['pagination']['perpage'] : null;
        $page = isset($params['pagination']['page']) ? $params['pagination']['page'] : null;

        // General search
        if (isset($params['query']['generalSearch'])){
            // Get first row from query to grab the keys/fields
            $firstRow = $this->query->get()->first();
            $firstRowArr = $firstRow->toArray();

            $searchValue = strtolower($params['query']['generalSearch']);

            $this->query->where(function ($q) use ($firstRow, $firstRowArr, $searchValue) {
                foreach ($firstRowArr as $entry => $value) {  
                    // If column name isnt in the models attributes, so its a relation
                    // Dynamically make wherehas clauses for generalsearch
                    if (!in_array($entry,array_keys($firstRow->getAttributes()))) {
                        $q->whereHas($entry, function ($q) use ($entry, $searchValue, $firstRow) {
                            $y = 0;
                            foreach ($firstRow->{$entry}->toArray() as $relatedEntry => $relatedValue) {
                                if(!is_array($relatedValue)){
                                    if ($y == 0){
                                        $q->where($relatedEntry,'LIKE','%'.strtolower($searchValue).'%');
                                    } 
                                    else {
                                        $q->orWhere($relatedEntry,'LIKE','%'.strtolower($searchValue).'%');
                                    }
                                }
                                $y++;
                            }
                        });
                    }
                }
                foreach ($firstRowArr as $entry => $value) {  
                    // Dynamically make where clauses for generalsearch
                    if(!is_array($value)){
                        $q->orWhere($entry,'LIKE','%'.strtolower($searchValue).'%');
                    }
                }
            });
        }

        // Generate collection from results
        $this->data = collect($this->query->get());

        // Sort the collection, nested columns will work too
        if (isset($params['sort'])) {
            $sortBy = ($params['sort']['sort'] == 'asc') ? 'sortBy' : 'sortByDesc';
            $this->data = $this->data->{$sortBy}($params['sort']['field']);
        }

        // Paginate the collection instead of query
        $total = count($this->data);
        if ($perPage){
            $pages = (int)ceil($total / $perPage);
            // If user is outside the pages range when changing pagination preferences
            if ($page > $pages) {
                // Set page to max possible page
                $page = $pages;
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
