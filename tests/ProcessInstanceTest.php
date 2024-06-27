<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;
use Nette\Utils\Random;

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
    public function test_get_with_parameter()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstances = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $params = [
            "processIsntanceIds" => [
                $processInstances->id,
            ]
        ];
        $processInstances = ProcessInstanceClient::get($params);

        $this->assertCount(1, $processInstances);
    }
    public function test_get_with_broken_parameter()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstances = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $mapmany = [];
        for ($i = 0; $i < 500; $i++) {
            $mapmany[] = $processInstances->id;
        }
        $params = [
            "processIsntanceIds" => $mapmany
        ];
        $processInstances = ProcessInstanceClient::get($params);

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

    public function test_get_with_busniessKey()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: '001');
        $processInstance = ProcessInstanceClient::findByBusniessKey('001');
        $this->assertEquals('001', $processInstance->businessKey);
    }


    public function test_get_with_variables()
    {
        $random = Random::generate();
        $variables = ['title' => ['value' => 'Foo' . $random, 'type' => 'string']];
        ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: '001');
        $processInstance = ProcessInstanceClient::getByVariables([
            [
                'name' => 'title',
                'operator' => "eq",
                'value' => $variables['title']['value'],
            ],
        ]);
        $this->assertCount(1, $processInstance);
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
