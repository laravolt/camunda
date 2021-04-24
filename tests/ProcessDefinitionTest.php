<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\ProcessDefinition;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\ProcessDefinitionClient;

class ProcessDefinitionTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->undeploySampleBpmn();
    }

    public function test_it_can_start_new_process_instance(): void
    {
        $this->deploySampleBpmn();

        $variables = ['title' => ['value' => 'Sample Title', 'type' => 'string']];
        $businessKey = 'key-1';
        $processInstance =
            ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: $businessKey);

        $this->assertIsString($processInstance->id);
    }

    public function test_it_cannot_start_with_empty_variables(): void
    {
        $this->deploySampleBpmn();
        $processDefinition = ProcessDefinitionClient::findByKey('process_1');

        $variables = [];
        $businessKey = 'key-1';

        $this->expectException(InvalidArgumentException::class);
        ProcessDefinitionClient::start($processDefinition->id, $variables, $businessKey);
    }

    public function test_it_can_get_list_process_definition(): void
    {
        $this->deploySampleBpmn();
        $processDefinitions = ProcessDefinitionClient::get();
        $this->assertCount(1, $processDefinitions);
        $this->assertInstanceOf(ProcessDefinition::class, $processDefinitions[0]);
    }

    public function test_it_can_find_by_id(): void
    {
        $this->deploySampleBpmn();
        $processDefinitions = ProcessDefinitionClient::get();
        $processDefinition = ProcessDefinitionClient::find($processDefinitions[0]->id);
        $this->assertNotNull($processDefinition);
    }

    public function test_it_cannot_find_by_invalid_id(): void
    {
        $this->expectException(ObjectNotFoundException::class);
        ProcessDefinitionClient::find('invalid-id');
    }

    public function test_it_can_find_by_key(): void
    {
        $this->deploySampleBpmn();
        $processDefinition = ProcessDefinitionClient::findByKey('process_1');
        $this->assertInstanceOf(ProcessDefinition::class, $processDefinition);
    }

    public function test_it_cannot_find_by_invalid_key(): void
    {
        $this->expectException(ObjectNotFoundException::class);
        ProcessDefinitionClient::findByKey('invalid-key');
    }

    public function test_it_can_get_xml_by_id(): void
    {
        $this->deploySampleBpmn();
        $processDefinitions = ProcessDefinitionClient::get();
        $id = $processDefinitions[0]->id;
        $xml = ProcessDefinitionClient::xml(id: $id);
        $this->assertNotNull($xml);
    }

    public function test_it_can_get_xml_by_key(): void
    {
        $this->deploySampleBpmn();
        $xml = ProcessDefinitionClient::xml(key: 'process_1');
        $this->assertNotNull($xml);
    }

    public function test_it_cannot_get_xml_without_id_or_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProcessDefinitionClient::xml();
    }
}
