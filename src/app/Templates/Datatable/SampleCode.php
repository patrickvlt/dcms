<?php

return '
/**
 * Generate JSON response for KTDatatable.
 *
 * @return \Illuminate\Http\JsonResponse
 */

public function fetch()
{
    $query = ' . $this->model . '::select(\'*\', \'name as something_name\')->selectRaw(\'column_one + column_two AS total_columns\')->with([\'relation\' => function ($query) {
        $query->select(\'*\');
    }]);

    // To simply select everything (don\'t use the get() method when instantiating a new DCMS Datatable class)
    // $query = ' . $this->model . '::query();

    // $searchInColumns = [\'id\',\'name\',\'email\'];
    // $excludeColumns = [\'password\',\'token\'];
    // return (new ' . $this->model . 'Datatable($query,$searchInColumns,$excludeColumns))->render();

    return (new ' . $this->model . 'Datatable($query))->render();
}

/**
 * Export visible data in Datatable.
 *
 * @return string
 */

public function export(): string
{
    // This is the visible data in the datatable, based on chosen filters/search results
    $data = request()->data;

    // Headers in the excel sheet
    // To select eager loaded/nested columns, use the collections` structure
    $headers = [
        "id" => "#",
        "name" => __("Name"),
        "user.posts.title" => __("Title"),
        "user.posts.category.name" => __("Category")
    ];

    // to export all columns and rows for this model
    // return $this->StoreExport();

    return $this->StoreExport($data,$headers);
}';
