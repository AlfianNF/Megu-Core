<?php

namespace App\Services\Auth;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DoLogin extends CoreService
{
    public $transaction = false;

    public function prepare($input)
    {
        $validator = Validator::make($input, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new CoreException($validator->errors()->first(), 422);
        }

        return $input;
    }

    public function process($input, $originalInput)
    {
        $user = Users::where('username', $input['username'])->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($input['password'], $user->password)) {
            throw new CoreException("Username atau Password salah", 401);
        }

        $token = auth('api')->login($user);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 
        ];
    }
}