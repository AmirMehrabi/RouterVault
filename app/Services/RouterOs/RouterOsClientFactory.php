<?php

namespace App\Services\RouterOs;

use App\Models\Router;
use RouterOS\Client;

class RouterOsClientFactory
{
    public function make(Router $router): Client
    {
        return new Client($router->routerOsConfig());
    }
}
