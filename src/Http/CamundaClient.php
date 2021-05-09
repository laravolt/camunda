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
        return Http::baseUrl(config('services.camunda.url'));
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
