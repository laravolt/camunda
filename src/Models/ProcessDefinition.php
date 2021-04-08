<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Models;

use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessDefinition extends CamundaModel
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

    public static function fromResponse(array $data)
    {
        return new self($data);
    }

    public function start(array $variables = [], string $businessKey = null): ProcessInstance
    {
        // At least one value must be set...
        if (empty($variables)) {
            throw new InvalidArgumentException('Cannot start process instance with empty variables');
        }

        $payload = ['variables' => $this->formatVariables($variables)];
        if ($businessKey) {
            $payload['businessKey'] = $businessKey;
        }
        $response = self::request()->asJson()->post("process-definition/{$this->id}/start", $payload);

        return new ProcessInstance($response->json());
    }

    public function xml()
    {
        return self::request()->get("process-definition/{$this->id}/xml")->json('bpmn20Xml');
    }

    public static function getList(): array
    {
        $response = self::request()->get("process-definition");
        $processDefinition = [];
        foreach ($response->json() as $res) {
            $processDefinition[] = self::fromResponse($res);
        }

        return $processDefinition;
    }

    public static function find(string $id): self
    {
        $response = self::request()->get("process-definition/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromResponse($response->json());
    }

    public static function findByKey(string $key): self
    {
        $response = self::request()->get("process-definition/key/$key");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromResponse($response->json());
    }

    public function getXml()
    {
        return $this->request('xml');
    }
}
