<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;

class SignalEventClient extends CamundaClient
{
    /**
     * Deliver a signal to every process instance and process definition that waits for it.
     *
     * @param  array<string, array{value: mixed, type?: string, valueInfo?: array}>  $variables
     */
    public static function throw(
        string $name,
        array $variables = [],
        ?string $executionId = null,
        ?string $tenantId = null,
        bool $withoutTenantId = false,
    ): bool {
        if ($name === '') {
            throw new InvalidArgumentException('Signal name cannot be empty');
        }

        $payload = ['name' => $name];
        if ($variables !== []) {
            self::guardVariables($variables);
            $payload['variables'] = $variables;
        }
        if ($executionId !== null) {
            $payload['executionId'] = $executionId;
        }
        if ($tenantId !== null) {
            $payload['tenantId'] = $tenantId;
        }
        if ($withoutTenantId) {
            $payload['withoutTenantId'] = true;
        }

        $response = self::make()->post('signal', $payload);

        if ($response->status() !== 204) {
            throw (new UnexpectedResponseException)->for('signal', $payload, (array) $response->json());
        }

        return true;
    }
}
