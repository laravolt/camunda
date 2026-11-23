<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\MessageCorrelationResult;
use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;

class MessageEventClient extends CamundaClient
{
    /**
     * Start a process instance through its message start event.
     *
     * Arguments: `messageName` and `businessKey` (both required), `variables` (optional).
     */
    public static function start(...$args): ProcessInstance
    {
        $variables = $args['variables'] ?? [];
        $messageName = $args['messageName'] ?? null;
        $businessKey = $args['businessKey'] ?? null;

        if (! $messageName) {
            throw new InvalidArgumentException('Arg messageName cannot be null');
        }

        if (! $businessKey) {
            throw new InvalidArgumentException('Arg businessKey cannot be null');
        }

        $results = self::correlate(
            messageName: $messageName,
            businessKey: $businessKey,
            processVariables: $variables,
        );

        // The engine prefers a waiting execution over a start event. A result without a
        // process instance means the message went to an existing instance instead.
        foreach ($results as $result) {
            if ($result->resultType === 'ProcessDefinition' && $result->processInstance !== null) {
                return $result->processInstance;
            }
        }

        throw new ObjectNotFoundException(sprintf('Message [%s] did not start a process instance', $messageName));
    }

    /**
     * Correlate a message to the executions waiting for it (intermediate catch events,
     * receive tasks) or to a message start event, as `POST /message` does.
     *
     * Narrow the target with `businessKey`, `processInstanceId` or `correlationKeys`;
     * set `all` to deliver the message to every match instead of exactly one.
     *
     * @param  array<string, array{value: mixed, type?: string, valueInfo?: array}>  $processVariables
     * @param  array<string, array{value: mixed, type?: string, valueInfo?: array}>  $correlationKeys
     * @return MessageCorrelationResult[]
     */
    public static function correlate(
        string $messageName,
        ?string $businessKey = null,
        ?string $processInstanceId = null,
        array $correlationKeys = [],
        array $processVariables = [],
        bool $all = false,
        ?string $tenantId = null,
        bool $withVariables = false,
    ): array {
        if ($messageName === '') {
            throw new InvalidArgumentException('Message name cannot be empty');
        }

        $payload = [
            'messageName' => $messageName,
            'all' => $all,
            'resultEnabled' => true,
            'variablesInResultEnabled' => $withVariables,
        ];
        if ($businessKey !== null) {
            $payload['businessKey'] = $businessKey;
        }
        if ($processInstanceId !== null) {
            $payload['processInstanceId'] = $processInstanceId;
        }
        if ($correlationKeys !== []) {
            self::guardVariables($correlationKeys);
            $payload['correlationKeys'] = $correlationKeys;
        }
        if ($processVariables !== []) {
            self::guardVariables($processVariables);
            $payload['processVariables'] = $processVariables;
        }
        if ($tenantId !== null) {
            $payload['tenantId'] = $tenantId;
        }

        $response = self::make()->post('message', $payload);

        if (! $response->successful()) {
            // Camunda answers 400 when no execution or start event matches the message.
            if ($response->status() === 400 && str_contains((string) $response->json('message'), 'No process definition or execution matches')) {
                throw new ObjectNotFoundException($response->json('message'));
            }

            throw (new UnexpectedResponseException)->for('message', $payload, (array) $response->json());
        }

        return array_map(
            fn (array $result) => MessageCorrelationResult::from($result),
            (array) $response->json(),
        );
    }
}
