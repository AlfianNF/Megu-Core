<?php

namespace App\CoreService;

use Exception;

class CoreException extends Exception
{
    protected $statusCode;

    public function __construct($message = "Internal Server Error", $statusCode = 400)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}