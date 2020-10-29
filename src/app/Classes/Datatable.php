<?php

/**
 * Created by Patrick Veltrop
 * Date: 18-09-2020
 * Time: 19:57
 */

namespace Pveltrop\DCMS\Classes;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class Datatable
{
    public function __construct($query, $searchFields=null, $excludeSearchFields=[]){
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

    public function filter($field=null, $value=null)
    {
        $this->query->where($field,'=',$value);
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

        // Prefix the parent class columns
        $parentModel = $this->query->getModel();
        $parentTable = (new $parentModel())->getTable();
        $parentColumns = Schema::getColumnListing($parentTable);

        // Build filters for query
        if (isset($params['query'])) {
            foreach ($params['query'] as $key => $value) {
                if ($key !== 'generalSearch'){
                    $this->filter($key,$value);
                }
            }
        }

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
            $this->data = $this->data->{$sortBy}($params['sort']['field']);
        }

        // Convert collection to array
        $this->data = array_values($this->data->toArray());

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
            foreach($searchColumns as $searchColumn){
                foreach($this->data as $dataKey => $dataRow){
                    try {
                        if (!in_array($searchColumn,$this->excludeSearchFields)){
                            if (preg_match('/'.strtolower($searchValue).'/m', strtolower($dataRow[$searchColumn])) > 0){
                                $newData[] = $dataRow;
                            }
                        }
                    } catch (\Throwable $th) {
                        //
                    }
                }
            }
            
            // Clear data if general search cant find anything
            // Or else the results wont be affected
            $this->data = (count($newData) >! 0) ? [] : $newData;

            if (count($this->data) > 0 && isset($perPage)){
                if (count($newData) > $perPage){
                    $page = 1;
                    $repaginate = array_chunk($this->data, $perPage, true);
                    $this->data = $repaginate[$page] ?? $this->data;
                    $pages = count($repaginate);
                } else {
                    $pages = 1;
                    $page = 1;
                }
            }
            $total = count($this->data);
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
