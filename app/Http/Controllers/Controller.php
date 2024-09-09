<?php

namespace App\Http\Controllers;

use App\Support\Response\Interfaces\ApiResponseInterface;
use App\Support\Response\Response;

abstract class Controller
{
    public Response $response;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->response = app()->make(ApiResponseInterface::class);
    }
}
