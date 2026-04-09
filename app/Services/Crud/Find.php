<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Find extends CoreService
{
    public function prepare($input)
    {
        if (!hasPermission("view-" . $input["model"])) throw new CoreException("Forbidden", 403);
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = "\\App\\Models\\" . Str::studly($input["model"]);
        
        if (!class_exists($classModel)) {
            throw new CoreException("Model " . $input["model"] . " tidak ditemukan", 404);
        }

        $modelInstance = new $classModel;
        $tableName = $modelInstance->getTable();

        $query = $classModel::query()->select($tableName . '.*');

        if (defined("$classModel::FIELD_RELATION") && !empty($classModel::FIELD_RELATION)) {
            foreach ($classModel::FIELD_RELATION as $foreignKey => $relation) {
                $linkTable = $relation['linkTable'];
                $linkField = $relation['linkField'];
                $alias = $relation['displayName'];
                
                $selectField = $relation['selectField'] ?? $linkField;

                $query->leftJoin($linkTable, "$tableName.$foreignKey", "=", "$linkTable.$linkField");

                $query->addSelect(DB::raw("$linkTable.$selectField as $alias"));
            }
        }

        $object = $query->where($tableName . '.id', $input["id"])->first();

        if (!$object) {
            throw new CoreException("Data tidak ditemukan", 404);
        }

        return $object;
    }
}