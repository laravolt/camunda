<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessDefinition;
use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessDefinitionClient extends CamundaClient
{
    public static function start(string $id, array $variables = [], string $businessKey = null): ProcessInstance
    {
        // At least one value must be set...
        if (empty($variables)) {
            throw new InvalidArgumentException('Cannot start process instance with empty variables');
        }

        $payload = ['variables' => self::formatVariables($variables)];
        if ($businessKey) {
            $payload['businessKey'] = $businessKey;
        }
        $response = self::make()->asJson()->post("process-definition/{$id}/start", $payload);

        return new ProcessInstance($response->json());
    }

    public static function xml(string $id = null, string $key = null): string
    {
        if ($id) {
            $path = "process-definition/$id/xml";
        } elseif ($key) {
            $path = "process-definition/key/$key/xml";
        } else {
            throw new InvalidArgumentException('Missing required parameter :id or :key');
        }

        return self::make()->get($path)->json('bpmn20Xml');
    }

    public static function get(): array
    {
        $processDefinition = [];
        foreach (self::make()->get("process-definition")->json() as $res) {
            $processDefinition[] = new ProcessDefinition($res);
        }

        return $processDefinition;
    }

    public static function find(string $id): ProcessDefinition
    {
        $response = self::make()->get("process-definition/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new ProcessDefinition($response->json());
    }

    public static function findByKey(string $key): ProcessDefinition
    {
        $response = self::make()->get("process-definition/key/$key");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new ProcessDefinition($response->json());
    }
}
