<?php

namespace App\Services\Crud;

use App\CoreService\CoreService;
use App\CoreService\CoreException;
use Illuminate\Support\Str;

class Get extends CoreService
{
    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::studly($model);

        if (!class_exists($classModel)) throw new CoreException("Model $model tidak ditemukan", 404);
        // if (!hasPermission("view-" . $model)) throw new CoreException("Forbidden", 403);

        $input["class_model"] = $classModel;
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];
        $query = $classModel::query();

        if (isset($input['search']) && $input['search'] != '') {
            $query->where(function($q) use ($classModel, $input) {
                foreach ($classModel::FIELD_LIST as $field) {
                    $q->orWhere($field, 'like', '%' . $input['search'] . '%');
                }
            });
        }

        foreach ($classModel::FIELD_LIST as $field) {
            if (isset($input[$field]) && $input[$field] != '') {
                $query->where($field, $input[$field]);
            }
        }

        $sortBy = $input['sort_by'] ?? 'id';
        $sortOrder = $input['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $limit = $input['limit'] ?? 10;
        return $query->paginate($limit);
    }
}