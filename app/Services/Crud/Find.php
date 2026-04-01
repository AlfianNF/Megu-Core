<?php

namespace App\Services\Crud;

use App\CoreService\CoreService;
use App\CoreService\CoreException;
use Illuminate\Support\Str;

class Find extends CoreService
{
    public function prepare($input)
    {
        // if (!hasPermission("view-" . $input["model"])) throw new CoreException("Forbidden", 403);
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = "\\App\\Models\\" . Str::studly($input["model"]);
        $object = $classModel::find($input["id"]);

        if (!$object) throw new CoreException("Data tidak ditemukan", 404);

        return $object;
    }
}