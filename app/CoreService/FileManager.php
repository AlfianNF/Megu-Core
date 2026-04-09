<?php

namespace App\CoreService;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManager
{
    public static function moveFromTemp($tempPath, $destinationFolder)
    {
        if (!$tempPath || !Storage::exists($tempPath)) {
            return $tempPath;
        }

        if (Str::contains($tempPath, 'public/tmp/')) {
            $filename = basename($tempPath);
            $newPath = "public/$destinationFolder/$filename";

            Storage::move($tempPath, $newPath);
            return $newPath;
        }

        return $tempPath;
    }
}