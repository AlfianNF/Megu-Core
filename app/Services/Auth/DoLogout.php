<?php

namespace App\Services\Auth;

use App\CoreService\CoreService;
use App\CoreService\CoreException;
use Illuminate\Support\Facades\Auth;

class DoLogout extends CoreService
{
    public $transaction = false;

    public function prepare($input)
    {
        if (!auth('api')->check()) {
            throw new CoreException("Anda belum login atau session sudah berakhir", 401);
        }
        return $input;
    }

    public function process($input, $originalInput)
    {
        try {
            auth('api')->logout();

            return [
                "success" => true,
                "message" => "Berhasil logout. Token telah dinonaktifkan."
            ];
        } catch (\Exception $e) {
            throw new CoreException("Gagal melakukan logout: " . $e->getMessage(), 500);
        }
    }
}