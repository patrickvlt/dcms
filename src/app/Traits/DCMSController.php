<?php

namespace Pveltrop\DCMS\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use App\Traits\DCMSCrud;
use App\Traits\DCMSImports;
use Pveltrop\DCMS\Classes\Form;
use Pveltrop\DCMS\Classes\Datatable;

trait DCMSController
{
    use DCMSCrud, DCMSImports;

    public function __init()
    {
        if (!app()->runningInConsole()) {
            $this->hasBooted = true;
            // Route prefix
            if (!isset($this->routePrefix)){
                throw new \RuntimeException("No routePrefix defined. Define this property in your controller constructor.");
            }
            // Get model and custom request class
            if (!isset($this->model)){
                throw new \RuntimeException("No model defined for: ".ucfirst($this->routePrefix)." in controller constructor.");
            }
            if (!isset($this->request)){
                throw new \RuntimeException("No custom request defined for: ".ucfirst($this->routePrefix)." in controller constructor.");
            } else {
                $this->request = (new $this->request());
            }

            // CRUD views
            $this->indexView = $this->views['index'] ?? 'index';
            $this->showView = $this->views['show'] ?? 'show';
            $this->editView = $this->views['edit'] ?? 'edit';
            $this->createView = $this->views['create'] ?? 'create';

            // JSON CRUD responses
            $this->createdUrl = $this->responses['created']['url'] ?? '/'.$this->routePrefix;
            $this->createdTitle = $this->responses['created']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('created');
            $this->createdMessage = $this->responses['created']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully created');
            $this->updatedUrl = $this->responses['updated']['url'] ?? '/'.$this->routePrefix;
            $this->updatedTitle = $this->responses['updated']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('updated');
            $this->updatedMessage = $this->responses['updated']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully updated');
            $this->deletedUrl = $this->responses['deleted']['url'] ?? '/'.$this->routePrefix;
            $this->deletedTitle = $this->responses['deleted']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('deleted');
            $this->deletedMessage = $this->responses['deleted']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully deleted');
            $this->confirmDeleteUrl = $this->responses['confirmDelete']['url'] ?? '/'.$this->routePrefix;
            $this->confirmDeleteTitle = $this->responses['confirmDelete']['title'] ?? __('Confirm deletion');
            $this->confirmDeleteMessage = $this->responses['confirmDelete']['message'] ?? __('Do you want to delete this object?');
            $this->failedDeleteUrl = $this->responses['failedDelete']['url'] ?? '/'.$this->routePrefix;
            $this->failedDeleteTitle = $this->responses['failedDelete']['title'] ?? __('Deletion failed');
            $this->failedDeleteMessage = $this->responses['failedDelete']['message'] ?? __('Failed to delete this object. An unknown error has occurred.');

            // jExcel imports
            $this->importFailedTitle = $this->jExcel['responses']['failed']['title'] ?? __('Import failed');
            $this->importFailedMessage = $this->jExcel['responses']['failed']['message'] ?? __('Some fields contain invalid data.');
            $this->importEmptyTitle = $this->jExcel['responses']['empty']['title'] ?? __('Import failed');
            $this->importEmptyMessage = $this->jExcel['responses']['empty']['message'] ?? __('Please fill in data to import.');
            $this->importFinishedTitle = $this->jExcel['responses']['finished']['title'] ?? __('Import finished');
            $this->importFinishedMessage = $this->jExcel['responses']['finished']['message'] ?? __('All data has been succesfully imported.');
            $this->importedUrl = $this->jExcel['responses']['imported']['url'] ?? '/'.$this->routePrefix;
            $this->importCols = $this->jExcel['columns'] ?? null;
            // jExcel autocorrect columns
            $this->autoFixColumns = $this->jExcel['autocorrect'] ?? null;

            // Check if DCMS config has a separate storage config for the current model
            $this->storageConfig = (config('dcms.storage.service.'.strtolower($this->routePrefix))) ? config('dcms.storage.service.'.strtolower($this->routePrefix)) : config('dcms.storage.service.global');
        }
    }

    public function index()
    {
        $this->__init();
        $vars = method_exists($this,'beforeIndex') ? $this->beforeIndex() : null;
        return view($this->routePrefix.'.'.$this->indexView)->with($vars);
    }

    public function fetch()
    {
        $this->__init();
        return (new Datatable((new $this->model)->query()))->render();
    }

    public function show($id)
    {
        $this->__init();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->{$this->routePrefix});

        $vars = method_exists($this,'beforeShow') ? $this->beforeShow($id) : null;
        return view($this->routePrefix.'.'.$this->showView,compact(${$this->routePrefix}))->with($vars);
    }

    public function edit($id)
    {
        $this->__init();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->{$this->routePrefix});
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
        $vars = method_exists($this,'beforeEdit') ? $this->beforeEdit($id) : null;
        return view($this->routePrefix.'.'.$this->editView,compact(${$this->routePrefix}))->with($vars)->with(['form' => $form]);
    }

    public function create()
    {
        $this->__init();
        $vars = method_exists($this,'beforeCreate') ? $this->beforeCreate() : null;
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
        return view($this->routePrefix.'.'.$this->createView)->with($vars)->with(['form' => $form]);
    }

    public function store()
    {
        return $this->crud('created');
    }

    public function update($id)
    {
        $id = request()->route()->parameters[$this->routePrefix];
        return $this->crud('updated',$id);
    }

    public function destroy($id)
    {
        if(!isset($this->hasBooted)){
            $this->__init();
        }
        $model = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);
        $passModel = $model;
        $model->delete();
        if (method_exists($this,'afterDestroy')){
            $this->afterDestroy($id,$passModel);
        }
    }

    public function destroyMultiple()
    {
        $this->__init();
        $deleteIDs = request()->deleteIDs;
        foreach ($deleteIDs as $id) {
            $this->destroy($id);
        }
    }
}
