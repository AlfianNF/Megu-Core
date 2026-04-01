<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Edit extends CoreService
{
    public $transaction = true;

    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::studly($model);
        
        if (!class_exists($classModel))
            throw new CoreException("Model $model tidak ditemukan", 404);

        // if (!hasPermission("update-" . $model))
        //     throw new CoreException("Forbidden", 403);

        $input["class_model"] = $classModel;
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];
        $object = $classModel::find($input["id"]);

        if (!$object) throw new CoreException("Data tidak ditemukan", 404);

        $rules = array_intersect_key($classModel::FIELD_VALIDATION, $input);
        $rules["id"] = "required";

        foreach ($rules as $key => $value) {
            $rules[$key] = str_replace('required', 'sometimes', $value);
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) throw new CoreException($validator->errors()->first(), 422);

        if (defined("$classModel::FIELD_UNIQUE") && $classModel::FIELD_UNIQUE) {
            foreach ($classModel::FIELD_UNIQUE as $searchFields) {
                $query = $classModel::where('id', '!=', $input['id']);
                $isChanged = false;

                foreach ($searchFields as $field) {
                    if (isset($input[$field]) && $input[$field] != $object->{$field}) {
                        $isChanged = true;
                    }
                    $query->where($field, $input[$field] ?? $object->{$field});
                }

                if ($isChanged && $query->exists()) {
                    throw new CoreException("Data " . implode(", ", $searchFields) . " sudah ada.", 422);
                }
            }
        }

        $input = $classModel::beforeUpdate($input);

        if (defined("$classModel::FIELD_UPLOAD")) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                if (array_key_exists($item, $input)) {
                    if (is_null($input[$item])) {
                        Storage::delete($object->{$item});
                        $object->{$item} = null;
                    } 
                    else if ($object->{$item} !== $input[$item]) {
                        $tmpPath = $input[$item];
                        if (Storage::exists($tmpPath)) {
                            $newPath = $classModel::FILEROOT . "/" . basename($tmpPath);
                            Storage::delete($object->{$item}); // Hapus lama
                            Storage::move($tmpPath, $newPath); // Pindah baru
                            $object->{$item} = $newPath;
                        }
                    }
                }
            }
        }

        foreach ($classModel::FIELD_EDIT as $field) {
            if ($field == "updated_by") {
                $object->updated_by = Auth::id();
            } elseif (array_key_exists($field, $input)) {
                if (!in_array($field, $classModel::FIELD_UPLOAD)) {
                    $object->{$field} = ($input[$field] !== '') ? $input[$field] : null;
                }
            }
        }

        $object->save();

        $afterResponse = $classModel::afterUpdate($object, $input);

        return [
            "data" => $object,
            "after_response" => $afterResponse,
            "message" => "Data berhasil diperbarui"
        ];
    }
}