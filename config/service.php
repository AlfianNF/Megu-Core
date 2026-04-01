<?php

return [
    // =========================================================================
    // GENERIC CRUD SERVICES
    // =========================================================================
    [
        "type" => "GET",
        "end_point" => "/get",
        "class" => "App\Services\Crud\Get"
    ],
    [
        "type" => "GET",
        "end_point" => "/dataset",
        "class" => "App\Services\Crud\Dataset"
    ],
    [
        "type" => "POST",
        "end_point" => "/create",
        "class" => "App\Services\Crud\Add"
    ],
    [
        "type" => "POST", // Atau PUT/PATCH sesuai preferensi
        "end_point" => "/update",
        "class" => "App\Services\Crud\Edit"
    ],
    [
        "type" => "POST", // Atau DELETE
        "end_point" => "/delete",
        "class" => "App\Services\Crud\Delete"
    ],
    [
        "type" => "GET",
        "end_point" => "/show",
        "class" => "App\Services\Crud\Find"
    ],
    [
        "type" => "POST",
        "end_point" => "/login",
        "class" => "App\Services\Auth\DoLogin"
    ],
    [
        "type" => "POST",
        "end_point" => "/logout",
        "class" => "App\Services\Auth\DoLogout"
    ],
];