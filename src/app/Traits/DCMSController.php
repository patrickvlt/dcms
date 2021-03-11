<?php

namespace Pveltrop\DCMS\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use App\Traits\DCMSCrud;
use App\Traits\DCMSImports;
use Illuminate\Http\JsonResponse;
use Pveltrop\DCMS\Classes\Form;
use Pveltrop\DCMS\Classes\Datatable;

trait DCMSController
{
    use DCMSCrud, DCMSImports;

    /**
     * Setup all dynamic properties for DCMS, required to define the correct CRUD and/or Datatable/import methods.
     */
    public function initDCMS(): void
    {
        if (!app()->runningInConsole()) {
            $this->hasBooted = true;
            // Route prefix
            if (!isset($this->routePrefix)) {
                throw new \RuntimeException("No routePrefix defined. Define this property in your controller constructor.");
            }
            // Get model and custom request class
            if (!isset($this->model)) {
                throw new \RuntimeException("No model defined for: ".ucfirst($this->routePrefix)." in controller constructor.");
            }
            if (!isset($this->request)) {
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
            $this->createdUrl = $this->responses['created']['url'] ?? null;
            $this->createdTitle = $this->responses['created']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('created');
            $this->createdMessage = $this->responses['created']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully created');
            $this->updatedUrl = $this->responses['updated']['url'] ?? null;
            $this->updatedTitle = $this->responses['updated']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('updated');
            $this->updatedMessage = $this->responses['updated']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully updated');
            $this->deletedUrl = $this->responses['deleted']['url'] ?? null;
            $this->deletedTitle = $this->responses['deleted']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('deleted');
            $this->deletedMessage = $this->responses['deleted']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully deleted');
            $this->confirmDeleteTitle = $this->responses['confirmDelete']['title'] ?? __('Confirm deletion');
            $this->confirmDeleteMessage = $this->responses['confirmDelete']['message'] ?? __('Do you want to delete this object?');
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
            $this->storageConfig = (config('dcms.storage.service.' . strtolower($this->routePrefix))) ?: config('dcms.storage.service.global');
        }
    }

    /**
     * Fetch records from the database, and render a response which can be handled by a Datatable.
     * @return JsonResponse
     */
    public function fetch()
    {
        $this->initDCMS();
        return (new Datatable((new $this->model)->query()))->render();
    }

    /**
     * Destroy multiple records in one go.
     */
    public function destroyMultiple()
    {
        $this->initDCMS();
        $deleteIDs = request()->deleteIDs;
        foreach ($deleteIDs as $id) {
            $this->destroy($id);
        }
    }

    public function index()
    {
        $this->initDCMS();
        $vars = method_exists($this, 'beforeIndex') ? $this->beforeIndex() : null;
        return view($this->indexView)->with($vars);
    }

    public function show($id)
    {
        $this->initDCMS();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ?: (new $this->model)->find(request()->{$this->routePrefix});

        $vars = method_exists($this, 'beforeShow') ? $this->beforeShow($id) : null;
        return view($this->showView, compact(${$this->routePrefix}))->with($vars);
    }

    public function edit($id)
    {
        $this->initDCMS();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ?: (new $this->model)->find(request()->{$this->routePrefix});
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        $vars = method_exists($this, 'beforeCreate') ? $this->beforeCreate() : [];
        $vars = method_exists($this, 'beforeCreateOrEdit') ? array_merge($vars,$this->beforeCreateOrEdit()) : $vars;
        return view($this->editView, compact(${$this->routePrefix}))->with($vars)->with(['form' => $form]);
    }

    public function create()
    {
        $this->initDCMS();
        $vars = method_exists($this, 'beforeCreate') ? $this->beforeCreate() : [];
        $vars = method_exists($this, 'beforeCreateOrEdit') ? array_merge($vars,$this->beforeCreateOrEdit()) : $vars;
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        return view($this->createView)->with($vars)->with(['form' => $form]);
    }

    public function store()
    {
        return $this->crud('created');
    }

    public function update($id)
    {
        $modelID = request()->route()->parameters[$this->routePrefix];
        return $this->crud('updated', $modelID);
    }

    public function destroy($id)
    {
        if (!isset($this->hasBooted)) {
            $this->initDCMS();
        }
        $model = ((new $this->model)->find($id)) ?: (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);
        $passModel = $model;
        $model->delete();
        if (method_exists($this, 'afterDestroy')) {
            $this->afterDestroy($id, $passModel);
        }
    }
}
