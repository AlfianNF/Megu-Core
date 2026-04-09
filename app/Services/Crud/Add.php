<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Add extends CoreService
{
    public $transaction = true;

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

        $validator = \Illuminate\Support\Facades\Validator::make($input, $classModel::FIELD_VALIDATION);
        if ($validator->fails()) throw new CoreException($validator->errors()->first(), 422);

        if (defined("$classModel::FIELD_UNIQUE") && !empty($classModel::FIELD_UNIQUE)) {
            foreach ($classModel::FIELD_UNIQUE as $searchFields) {
                $query = $classModel::query();
                foreach ($searchFields as $field) {
                    $query->where($field, $input[$field] ?? null);
                }
                if ($query->exists()) throw new CoreException("Data " . implode(", ", $searchFields) . " sudah ada.", 422);
            }
        }

        $input = $classModel::beforeInsert($input);

        if (defined("$classModel::FIELD_UPLOAD") && !empty($classModel::FIELD_UPLOAD)) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                // Cek apakah input mengandung path string (hasil upload-tmp)
                if (isset($input[$item]) && is_string($input[$item])) {
                    $currentPath = $input[$item];

                    // Jika file berada di folder 'tmp', pindahkan ke folder permanen
                    if (str_starts_with($currentPath, 'tmp/')) {
                        if (Storage::disk('public')->exists($currentPath)) {
                            $filename = basename($currentPath);
                            $destFolder = defined("$classModel::FILEROOT") ? $classModel::FILEROOT : 'uploads';
                            $newPath = $destFolder . '/' . $filename;

                            // Pindahkan file secara fisik
                            Storage::disk('public')->move($currentPath, $newPath);

                            // Update data yang akan disimpan ke database
                            $input[$item] = $newPath;
                        }
                    }
                }
            }
        }

        $data = array_intersect_key($input, array_flip($classModel::FIELD_ADD));
        
        if (in_array('created_by', $classModel::FIELD_ADD)) {
            $data['created_by'] = Auth::id();
        }

        $object = $classModel::create($data);

        $classModel::afterInsert($object, $input);

        return [
            "data" => $object,
            "message" => "Data berhasil disimpan"
        ];
    }
}