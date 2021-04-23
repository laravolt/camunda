<?php

namespace Laravolt\Camunda\Tests\Models;

use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Tests\TestCase;

class ProcessDefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_start_new_process_instance(): void
    {
        $this->deploySampleBpmn();
        $processDefinition = ProcessDefinitionClient::findByKey('process_1');

        $variables = ['title' => ['value' => 'Sample Title', 'type' => 'string']];
        $businessKey = 'key-1';
        $processInstance = ProcessDefinitionClient::start($processDefinition->id, $variables, $businessKey);

        $this->assertIsString($processInstance->id);

        $this->undeploySampleBpmn();
    }

    /**
     * @test
     */
    // public function it_can_get_list_process_definition(): void
    // {
    //     $processDefinitions = ProcessDefinition::getList();
    // }
}
