<?php

namespace Laravolt\Camunda;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CamundaClient
{
    public static function make(): PendingRequest
    {
        return Http::baseUrl(config('services.camunda.url'));
    }
}
