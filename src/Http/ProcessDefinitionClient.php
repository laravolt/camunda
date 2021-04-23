<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessDefinitionClient extends CamundaClient
{
    public string $key;
    public string $category;
    public ?string $name;
    public ?string $description;
    public int $version;
    public string $resource;
    public string $deploymentId;
    public ?string $diagram;
    public bool $suspended;
    public ?string $tenantId;
    public ?string $versionTag;
    public ?string $historyTimeToLive;
    public bool $startableInTasklist;

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

    public function xml()
    {
        return self::make()->get("process-definition/{$this->id}/xml")->json('bpmn20Xml');
    }

    public static function getList(): array
    {
        $response = self::make()->get("process-definition");
        $processDefinition = [];
        foreach ($response->json() as $res) {
            $processDefinition[] = self::fromResponse($res);
        }

        return $processDefinition;
    }

    public static function find(string $id): self
    {
        $response = self::make()->get("process-definition/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromResponse($response->json());
    }

    public static function findByKey(string $key): \Laravolt\Camunda\Dto\ProcessDefinition
    {
        $response = self::make()->get("process-definition/key/$key");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new \Laravolt\Camunda\Dto\ProcessDefinition($response->json());
    }

    public function getXml()
    {
        return $this->request('xml');
    }
}
