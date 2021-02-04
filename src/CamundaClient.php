<?php

namespace Laravolt\Camunda;

use Illuminate\Support\Facades\Http;

class CamundaClient
{
    public static function make()
    {
        return Http::baseUrl(config('services.camunda.url'));
    }
}
