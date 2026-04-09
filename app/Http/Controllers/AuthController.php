<?php

namespace App\Http\Controllers;

use App\CoreService\CallService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $input = $request->all();
        
        return CallService::run("DoLogin", $input);
    }

    public function logout()
    {
        return CallService::run("DoLogout", []);
    }
}