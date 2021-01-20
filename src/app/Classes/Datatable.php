<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

use ReflectionClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;

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
     * Build query filters, this has to be done in different ways
     * if the base query isnt an instance of Builder
     *
     * @return void
     */
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
        $this->queryBuilder = class_basename($this->query) == 'Builder' ? true : false;

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
            $this->searchValue = strtolower($this->params['query']['generalSearch']);

            if ($this->queryBuilder){
                $this->queryModel = $this->query->getModel();
                $this->model = new $this->queryModel();
                $this->table = $this->queryModel->getTable();
                $this->columns = Schema::getColumnListing($this->table);
                $this->relations = (is_countable($this->query->getEagerLoads()) && $this->query->getEagerLoads() > 0) ? array_keys($this->query->getEagerLoads()) : null;
                $this->usedOuterWhere = false;

                /**
                 * Make where clauses from relations
                 */
                if($this->relations){
                    foreach ($this->relations as $x => $relationName) {
                        $this->relationName = $relationName;
                        $innerWhere = ($x > 0) ? 'orWhere' : 'where';
                        $this->query->{$innerWhere}(function ($q) {
                            $this->usedInnerWhere = false;
                            // To make dynamic where clauses for any relation, the array keys are needed
                            // The relations' keys will be fetched with Schema
                            $q->whereHas($this->relationName, function ($q) {
                                try {
                                    $relationMethod = new \ReflectionClass($this->model->{$this->relationName}());
                                    $relationMethod = $relationMethod->getName();
                                } catch (\Throwable $th) {
                                    $relationMethod = null;
                                }
                                if (preg_match('/Relation/', $relationMethod)) {
                                    $relationMethod = $this->model->{$this->relationName}();
                                    $relationClass = $relationMethod->getRelated();
                                    $relationTable = $relationClass->getTable();
                                    $relationProps = Schema::getColumnListing($relationTable);
                                    $relation = array_flip($relationProps);
        
                                    foreach ($relation as $relatedEntry => $relatedValue) {
                                        if (!is_array($relatedValue)) {
                                            $thisInnerWhere = ($this->usedInnerWhere) ? 'orWhere' : 'where';
                                            $q->{$thisInnerWhere}($relationTable . '.' . $relatedEntry, 'LIKE', '%' . strtolower($this->searchValue) . '%');
                                            $this->usedInnerWhere = true;
                                        }
                                        $this->usedOuterWhere = true;
                                    }
                                }
                            });
                        });
                    }
                }

                $outerWhere = ($this->usedOuterWhere) ? 'orWhere' : 'where';
                
                /**
                 * Make where clauses from query models' table
                 */
                if($this->columns){
                    $this->query->{$outerWhere}(function ($q) {
                        foreach ($this->columns as $z => $column) {
                            // Dynamically make where clauses for generalsearch
                            // These are the models' default properties
                            if(!is_array($column)){
                                $finalInnerWhere = ($z > 0) ? 'orWhere' : 'where';
                                $q->{$finalInnerWhere}($column,'LIKE','%'.strtolower($this->searchValue).'%');
                            }
                        }
                    });
                }
            } else {
                $fetchData = $this->query;
                $this->query = [];
                /**
                 * Search for the user input by encoding the rows in JSON, 
                 * and matching with RegEx (this is a lot slower than working with a Builder instance, use this for smaller amounts of data)
                 */
                foreach($fetchData as $dataKey => $dataRow){
                    $searchRe = '/\:(\"|)'.strtolower($this->searchValue).'.*?(\,)/m';
                    $searchIn = strtolower(json_encode($dataRow));
                    if (preg_match($searchRe,$searchIn) > 0){
                        $this->query[] = $dataRow;
                    }
                }
            }
        }
        
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
