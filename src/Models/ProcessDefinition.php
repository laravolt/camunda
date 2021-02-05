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

        $payload = ['variables' => $variables];
        if ($businessKey) {
            $payload['businessKey'] = $businessKey;
        }
        $response = self::request()->asJson()->post("process-definition/{$this->id}/start", $payload);

        return new ProcessInstance([
            'id' => $response->json('id'),
            'businessKey' => $response->json('businessKey'),
        ]);
    }

    public function xml()
    {
        return $this->get('xml')->bpmn20Xml;
    }

    public static function get(string $id): self
    {
        $response = self::request()->get("process-definition/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromResponse($response->json());
    }

    public static function getByKey(string $key): self
    {
        $response = self::request()->get("process-definition/key/$key");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromResponse($response->json());
    }

    public function getXml()
    {
        return $this->get('xml');
    }

    public function getStartTaskName()
    {
        $xml = $this->getXml()->bpmn20Xml;

        $parser = new \SimpleXMLElement($xml);
        $parser->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
        $parser->registerXPathNamespace('camunda', 'http://camunda.org/schema/1.0/bpmn');

        $startTaskName = $parser->xpath('//bpmn:startEvent');

        return (string)data_get($startTaskName, '0')['id'] ?? false;
    }
}
