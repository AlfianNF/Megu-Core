<?php

namespace App\Http\Controllers;

use App\CoreService\CallService;
use Illuminate\Http\Request;
use App\CoreService\CoreExport;
use Maatwebsite\Excel\Facades\Excel;

class CrudController extends Controller
{
    public function callService(Request $request, $serviceName)
    {
        $input = $request->all();
        
        $params = $request->route()->parameters();
        $input = array_merge($input, $params);

        return CallService::run($serviceName, $input);
    }

    public function index($model)
    {
        return CallService::run("Get", ["model" => $model, ...request()->all()]);
    }


    public function show($model, $id)
    {
        return CallService::run("Find", ["model" => $model, "id" => $id, ...request()->all()]);
    }

    public function create($model)
    {
        return CallService::run("Add", ["model" => $model, ...request()->all()]);
    }

    public function update($model)
    {
        return CallService::run("Edit", ["model" => $model, ...request()->all()]);
    }

    public function delete($model)
    {
        return CallService::run("Delete", ["model" => $model, ...request()->all()]);
    }



    public function export(Request $request, $model)
    {
        $request->merge([
            'limit' => 1000, 
            'model' => $model 
        ]); 
        
        $response = (new \App\Services\Crud\Get)->execute($request->all());

        $data = $response->items(); 

        $classModel = "\\App\\Models\\" . \Illuminate\Support\Str::studly($model);
        $fields = defined("$classModel::FIELD_LIST") ? $classModel::FIELD_LIST : ['id', 'created_at'];

        $fileName = 'export_' . $model . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\CoreService\CoreExport(collect($data), $fields), 
            $fileName
        );
    }

    public function generate($model)
    {
        $classModel = "\\App\\Models\\" . \Illuminate\Support\Str::studly($model);
        if (!class_exists($classModel)) return response()->json(["message" => "Not Found"], 404);

        return response()->json([
            "model" => $model,
            "config" => [
                "table" => $classModel::TABLE,
                "field_add" => $classModel::FIELD_ADD,
                "field_edit" => $classModel::FIELD_EDIT,
                "field_validation" => $classModel::FIELD_VALIDATION,
                "field_relation" => $classModel::FIELD_RELATION,
            ]
        ]);
    }
}