<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;

class ModelController extends Controller
{
    // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $models = [];
        foreach (GetModels() as $x => $model) {
            $class = $model['class'];
            $retrievedModel = (new $class());
            $models[$x]['class'] = $model['class'];
            $models[$x]['table'] = $retrievedModel->getTable();
            $models[$x]['model'] = $retrievedModel;
        }
        $models = collect($models);
        return (new Datatable($models))->render();
    }

    public function index()
    {
        return view('dcms::model.index');
    }

    public function create()
    {
        return view('dcms::model.create');
    }

    // public function afterCreate($request, $model)
    // {
    //     event(new Registered($model));
    // }

    // public function create()
    // {
    //     $this->__init();
    //     // Auto generated Form with HTMLTag package
    //     $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
    //     return view('dcms::model.crud')->with(['form' => $form]);
    // }

    // public function edit(User $model)
    // {
    //     $this->__init();
    //     // Auto generated Form with HTMLTag package
    //     $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
    //     return view('dcms::model.crud')->with(['form' => $form]);
    // }
}
