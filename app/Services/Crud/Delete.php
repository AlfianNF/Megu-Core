<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Delete extends CoreService
{
    public $transaction = true;

    public function prepare($input)
    {
        if (!hasPermission("delete-" . $input["model"])) {
            dd($input);
            throw new CoreException("Forbidden", 403);
        }
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = "\\App\\Models\\" . Str::studly($input["model"]);
        $object = $classModel::find($input["id"] ?? null);

        if (!$object) {
            throw new CoreException("Data tidak ditemukan", 404);
        }

        $classModel::beforeDelete($input);

        if (defined("$classModel::FIELD_UPLOAD") && !empty($classModel::FIELD_UPLOAD)) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                if ($object->{$item}) {
                    Storage::delete($object->{$item});
                }
            }
        }

        $object->delete();

        $classModel::afterDelete($object, $input);

        return [
            "message" => "Data " . $input["model"] . " berhasil dihapus"
        ];
    }
}