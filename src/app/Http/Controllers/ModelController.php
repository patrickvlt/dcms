<?php

namespace Pveltrop\DCMS\Http\Controllers;

use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Crud;
use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;
use Pveltrop\DCMS\Http\Requests\ModelRequest;

class ModelController extends Controller
{
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

    public function store(Request $request)
    {
         $formatRequest = $request->all();

         $formatRequest['responses'] = json_decode($formatRequest['responses'],true);
         $formatRequest['views'] = json_decode($formatRequest['views'],true);
         $formatRequest['columns'] = json_decode($formatRequest['columns'],true);
         $formatRequest['ktColumns'] = json_decode($formatRequest['ktColumns'],true);
         $formatRequest['jExcelColumns'] = json_decode($formatRequest['jExcelColumns'],true);
         $formatRequest['jExcelResponses'] = json_decode($formatRequest['jExcelResponses'],true);

        $this->customRequest = new \Illuminate\Http\Request();
        $this->customRequest->setMethod('POST');

        $this->customRequest->request->add($formatRequest);

        $models = [];
        foreach (GetModels() as $key => $model) {
            $models[] = $model['file'];
        }

        if (in_array($this->customRequest['name'], $models)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['name' => ['This model already exists.']]], 422);
        }

        $this->customRequest = $this->validate($this->customRequest, (new ModelRequest())->rules());

        $this->generateCRUD($this->customRequest);
        $this->generateViews($this->customRequest);
    }

    public function generateCRUD($customRequest)
    {
        (new Crud())->generate($customRequest);
    }

    public function generateViews($customRequest)
    {
        $views = $customRequest['views'];
        foreach ($views as $x => $path) {
            $file = resource_path() . '/views/' . str_replace('.', '/', $path) . '.blade.php';
            $folder = preg_replace('/\/[^]\/[^\s]*\.blade\.php/m', '', $file);
            if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }
            // Modify the content
            if ($x == 'create' || $x == 'edit' || $x == 'show') {
                $newContent = include __DIR__ . '/../../Templates/Crud/Views/Crud.php';
            } elseif ($x == 'index') {
                $newContent = include __DIR__ . '/../../Templates/Crud/Views/Index.php';
            }
            // Write to file
            file_put_contents($file, '');
            file_put_contents($file, $newContent);
        }
    }
}
