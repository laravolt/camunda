<?php

namespace Laravolt\Camunda\Tests\Models;

use Laravolt\Camunda\Models\ProcessDefinition;
use Laravolt\Camunda\Models\ProcessInstance;
use Laravolt\Camunda\Tests\TestCase;

class ProcessDefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_start_new_process_instance(): void
    {
        $this->deploySampleBpmn();
        $processDefinition = ProcessDefinition::findByKey('process_1');

        $variables = ['title' => ['value' => 'Sample Title', 'type' => 'string']];
        $businessKey = 'key-1';
        $processInstance = $processDefinition->start($variables, $businessKey);

        $this->assertInstanceOf(ProcessInstance::class, $processInstance);
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
