<?php

namespace App\Http\Controllers;

use App\CoreService\CallService;
use Illuminate\Http\Request;

class CrudController extends Controller
{
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