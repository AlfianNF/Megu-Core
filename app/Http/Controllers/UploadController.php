<?php

namespace App\Http\Controllers;

use App\CoreService\CoreException;
use App\CoreService\CoreResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
   
    public function uploadTmp(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(["message" => "File harus dilampirkan"], 422);
        }

        $file = $request->file('file');
        $originalname = $file->getClientOriginalName();

        // Logika Rename jika file dengan nama yang sama sudah ada di Tmp
        if (Storage::exists("tmp/" . $originalname)) {
            $id = 1;
            $filenameOnly = pathinfo($originalname, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            while (true) {
                $originalname = $filenameOnly . "($id)." . $extension;
                if (!Storage::exists("tmp/" . $originalname)) break;
                $id++;
            }
        }

        // Simpan ke folder 'tmp' di disk 'public'
        $path = $file->storeAs('tmp', $originalname, 'public');

        return CoreResponse::ok([
            "filename" => $originalname,
            "path" => $path, // Ini yang akan dikirim user ke API CRUD
            "url" => Storage::url($path),
            "ext" => $file->getClientOriginalExtension()
        ]);
    }

    
    public function getFile($model, $field, $id)
    {
        // 1. Cari Class Model secara dinamis
        $classModel = "\\App\\Models\\" . Str::studly($model);
        if (!class_exists($classModel)) throw new CoreException("Model tidak ditemukan", 404);

        $modelInstance = new $classModel;
        $tableName = $modelInstance->getTable();

        // 2. Ambil path file dari database
        $data = DB::table($tableName)->where('id', $id)->value($field);

        // 3. Cek keberadaan file di storage (disk public)
        if ($data && Storage::disk('public')->exists($data)) {
            $file = Storage::disk('public')->get($data);
            $type = Storage::disk('public')->mimeType($data);
        } else {
            // Jika tidak ada, tampilkan gambar default "not found"
            return $this->getDefaultImage();
        }

        return Response::make($file, 200)->header("Content-Type", $type);
    }

    public function getTumbnailFile($model, $field, $id)
    {
        $classModel = "\\App\\Models\\" . Str::studly($model);
        if (!class_exists($classModel)) throw new CoreException("Model tidak ditemukan", 404);

        $modelInstance = new $classModel;
        $tableName = $modelInstance->getTable();

        $data = DB::table($tableName)->where('id', $id)->value($field);

        if ($data && Storage::disk('public')->exists($data)) {
            $file = Storage::disk('public')->get($data);
            $type = Storage::disk('public')->mimeType($data);
        } else {
            return $this->getDefaultImage(true); // Return thumbnail default
        }

        // 4. Manipulasi Gambar menggunakan Intervention Image
        // Resize ke 100x100 (atau sesuai kebutuhan)
        $img = Image::make($file)->fit(100, 100);

        return $img->response($type);
    }

    private function getDefaultImage($isThumbnail = false)
    {
        // Pastikan kamu punya file ini di storage/app/public/default/notfound.png
        $path = "default/notfound.png";
        
        if (!Storage::disk('public')->exists($path)) {
            // Jika file notfound.png pun tidak ada, return error transparan
            return response()->make('', 404);
        }

        $file = Storage::disk('public')->get($path);
        $type = Storage::disk('public')->mimeType($path);

        if ($isThumbnail) {
            return Image::make($file)->fit(100, 100)->response($type);
        }

        return Response::make($file, 200)->header("Content-Type", $type);
    }
}
