<?php

namespace App\Services;

use Illuminate\Support\Str;

abstract class BaseService
{
    public $transaction = false; 

    protected function getModel($input)
    {
        $modelName = Str::studly($input['model']);
        $classModel = "\\App\\Models\\" . $modelName;

        if (!class_exists($classModel)) {
            throw new \Exception("Model $modelName tidak ditemukan.");
        }
        return $classModel;
    }
}