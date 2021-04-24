<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessDefinition;
use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessDefinitionClient extends CamundaClient
{
    public static function start(...$args): ProcessInstance
    {
        $variables = $args['variables'] ?? [];
        $businessKey = $args['businessKey'] ?? null;

        // At least one value must be set...
        if (empty($variables)) {
            throw new InvalidArgumentException('Cannot start process instance with empty variables');
        }

        $payload = ['variables' => self::formatVariables($variables)];
        if ($businessKey) {
            $payload['businessKey'] = $businessKey;
        }

        $path = self::makeIdentifierPath('process-definition/{identifier}/start', $args);
        $response = self::make()->asJson()->post($path, $payload);

        return new ProcessInstance($response->json());
    }

    public static function xml(...$args): string
    {
        $path = self::makeIdentifierPath(path: 'process-definition/{identifier}/xml', args: $args);

        return self::make()->get($path)->json('bpmn20Xml');
    }

    public static function get(): array
    {
        $processDefinition = [];
        foreach (self::make()->get('process-definition')->json() as $res) {
            $processDefinition[] = new ProcessDefinition($res);
        }

        return $processDefinition;
    }

    public static function find(...$args): ProcessDefinition
    {
        $response = self::make()->get(self::makeIdentifierPath('process-definition/{identifier}', $args));

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new ProcessDefinition($response->json());
    }
}
