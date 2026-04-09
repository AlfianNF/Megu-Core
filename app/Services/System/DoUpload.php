<?php

namespace App\Services\System;

use App\CoreService\CoreService;
use App\CoreService\CoreException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoUpload extends CoreService
{
    public function prepare($input)
    {
        if (!request()->hasFile('file')) {
            throw new CoreException("Tidak ada file yang diunggah", 400);
        }
        return $input;
    }

    public function process($input, $originalInput)
    {
        $file = request()->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        
        $path = $file->storeAs('public/tmp', $filename);

        return [
            "success" => true,
            "temporary_path" => $path, 
            "filename" => $filename
        ];
    }
}