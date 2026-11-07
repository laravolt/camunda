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
        $identifier = $args['id'] ? self::segment((string) $args['id']) : false;
        if ($args['key']) {
            $identifier = 'key/'.self::segment((string) $args['key']);
            if ($args['tenantId']) {
                $identifier .= '/tenant-id/'.self::segment((string) $args['tenantId']);
            }
        }

        if (! $identifier) {
            throw new InvalidArgumentException('');
        }

        return str_replace('{identifier}', $identifier, $path);
    }

    /**
     * Camunda variable types this client forwards (compared case-insensitively).
     */
    protected const VARIABLE_TYPES = [
        'boolean', 'bytes', 'short', 'integer', 'long', 'double', 'date',
        'string', 'null', 'object', 'json', 'xml', 'file',
    ];

    /**
     * Serialization formats accepted for Object variables. Java serialization is not.
     */
    protected const OBJECT_FORMATS = ['application/json', 'application/xml'];

    /**
     * Reject variables whose declared type or serialization format the client does not forward.
     *
     * @throws InvalidArgumentException
     */
    protected static function guardVariables(mixed $variables): void
    {
        if (! is_iterable($variables)) {
            return;
        }

        foreach ($variables as $name => $variable) {
            if (! is_array($variable) || ! array_key_exists('type', $variable) || $variable['type'] === null) {
                continue;
            }

            $type = strtolower((string) $variable['type']);
            if (! in_array($type, self::VARIABLE_TYPES, true)) {
                throw new InvalidArgumentException(sprintf('Variable [%s] has an unsupported type.', $name));
            }

            $format = $variable['valueInfo']['serializationDataFormat'] ?? null;
            if ($format !== null && ! in_array(strtolower((string) $format), self::OBJECT_FORMATS, true)) {
                throw new InvalidArgumentException(sprintf('Variable [%s] has an unsupported serialization format.', $name));
            }
        }
    }

    /**
     * Encode a caller-supplied value for use as one URL path segment.
     */
    protected static function segment(string $value): string
    {
        return rawurlencode($value);
    }
}
