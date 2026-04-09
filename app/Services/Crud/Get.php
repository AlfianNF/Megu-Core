<?php

namespace App\Services\Crud;

use App\CoreService\CoreService;
use App\CoreService\CoreException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Get extends CoreService
{
    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::studly($model);

        if (!class_exists($classModel)) throw new CoreException("Model $model tidak ditemukan", 404);
        
        $input["class_model"] = $classModel;
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];
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

        if (isset($input['search']) && $input['search'] != '') {
            $query->where(function($q) use ($classModel, $input, $tableName) {
                foreach ($classModel::FIELD_LIST as $field) {
                    $q->orWhere($tableName . '.' . $field, 'like', '%' . $input['search'] . '%');
                }
            });
        }

        foreach ($classModel::FIELD_LIST as $field) {
            if (isset($input[$field]) && $input[$field] != '') {
                $query->where($tableName . '.' . $field, $input[$field]);
            }
        }

        $sortBy = $input['sort_by'] ?? $tableName . '.id';
        $sortOrder = $input['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $limit = $input['limit'] ?? 10;
        return $query->paginate($limit);
    }
}