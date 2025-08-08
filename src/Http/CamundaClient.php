<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;

class CamundaClient
{
    public static function make(): PendingRequest
    {
        $request = Http::baseUrl(config('services.camunda.url'));

        $user = config('services.camunda.user');
        $password = config('services.camunda.password');
        if ($user && $password) {
            $request->withBasicAuth($user, $password);
        }

        return $request;
    }

    protected static function makeIdentifierPath(string $path, array $args): string
    {
        // If no named parameters defined, we assume it is an ID
        if (count($args) === 1 && isset($args[0])) {
            $args['id'] = $args[0];
        }

        $args += ['id' => false, 'key' => false, 'tenantId' => false];
        $identifier = $args['id'];
        if ($args['key']) {
            $identifier = 'key/'.$args['key'];
            if ($args['tenantId']) {
                $identifier .= '/tenant-id/'.$args['tenantId'];
            }
        }

        if (! $identifier) {
            throw new InvalidArgumentException('');
        }

        return str_replace('{identifier}', $identifier, $path);
    }
}
