<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;

class ProcessInstanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deploySampleBpmn();
    }

    public function test_find_by_id()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance1 = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $processInstance2 = ProcessInstanceClient::find(id: $processInstance1->id);
        $processInstance3 = ProcessInstanceClient::find($processInstance1->id);

        $this->assertEquals($processInstance1->id, $processInstance2->id);
        $this->assertEquals($processInstance2->id, $processInstance3->id);
    }

    public function test_get()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $processInstances = ProcessInstanceClient::get();

        $this->assertCount(1, $processInstances);
    }

    public function test_get_by_parameters()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: '001');

        $processInstances = ProcessInstanceClient::get(['businessKey' => '001']);
        $this->assertCount(1, $processInstances);

        $processInstances = ProcessInstanceClient::get(['businessKey' => '002']);
        $this->assertCount(0, $processInstances);
    }

    public function test_get_variables()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $variables = ProcessInstanceClient::variables($processInstance->id);

        $this->assertCount(1, $variables);
        $this->assertInstanceOf(Variable::class, $variables['title']);
        $this->assertEquals('String', $variables['title']->type);
        $this->assertEquals('Foo', $variables['title']->value);
    }

    public function test_delete()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $deleted = ProcessInstanceClient::delete($processInstance->id);
        $this->assertTrue($deleted);

        $this->expectException(ObjectNotFoundException::class);
        ProcessInstanceClient::find($processInstance->id);
    }

    protected function tearDown(): void
    {
        $this->truncateDeployment();
    }
}
