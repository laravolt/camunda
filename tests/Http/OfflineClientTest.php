<?php

namespace Laravolt\Camunda\Tests\Http;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Laravolt\Camunda\Collections\VariableCollection;
use Laravolt\Camunda\Dto\Deployment;
use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Dto\ProcessDefinition;
use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\DeploymentClient;
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;
use Laravolt\Camunda\Tests\TestCase;

/**
 * Offline smoke tests: run the HTTP clients and DTO hydration against Http::fake(),
 * so src/ is exercised (and checked for deprecations) without a Camunda server.
 * Response bodies are trimmed-down shapes of the Camunda 7 REST API.
 */
class OfflineClientTest extends TestCase
{
    private const BASE = 'http://camunda.test/engine-rest';

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('services.camunda.url', self::BASE);
    }

    public function test_process_definition_find_and_xml(): void
    {
        Http::fake([
            self::BASE.'/process-definition/key/process_1' => Http::response($this->processDefinition()),
            self::BASE.'/process-definition/key/process_1/xml' => Http::response(['id' => 'pd-1', 'bpmn20Xml' => '<xml/>']),
        ]);

        $definition = ProcessDefinitionClient::find(key: 'process_1');

        $this->assertInstanceOf(ProcessDefinition::class, $definition);
        $this->assertSame('process_1', $definition->key);
        $this->assertSame('<xml/>', ProcessDefinitionClient::xml(key: 'process_1'));
    }

    public function test_process_definition_not_found(): void
    {
        Http::fake([
            self::BASE.'/process-definition/missing' => Http::response(['type' => 'RestException', 'message' => 'No matching definition'], 404),
        ]);

        $this->expectException(ObjectNotFoundException::class);
        ProcessDefinitionClient::find(id: 'missing');
    }

    public function test_start_process_instance_with_variables(): void
    {
        Http::fake([
            self::BASE.'/process-definition/key/process_1/start' => Http::response($this->processInstance()),
        ]);

        $variables = (new VariableCollection(['title' => 'Foo', 'isActive' => true, 'tags' => ['a', 'b']]))->toArray();
        $instance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: 'bk-1');

        $this->assertInstanceOf(ProcessInstance::class, $instance);
        $this->assertSame('pi-1', $instance->id);
        Http::assertSent(fn ($request) => $request['businessKey'] === 'bk-1'
            && $request['variables']['isActive']['type'] === 'Boolean'
            && $request['variables']['tags']['type'] === 'Json');
    }

    public function test_process_instance_variables(): void
    {
        Http::fake([
            self::BASE.'/process-instance/pi-1/variables*' => Http::response([
                'title' => ['type' => 'String', 'value' => 'Foo', 'valueInfo' => []],
                'isActive' => ['type' => 'Boolean', 'value' => true],
            ]),
        ]);

        $variables = ProcessInstanceClient::variables('pi-1');

        $this->assertInstanceOf(Variable::class, $variables['title']);
        $this->assertSame('Boolean', $variables['isActive']->type);
        $this->assertSame([], $variables['isActive']->valueInfo);
    }

    public function test_deployment_find_casts_deployment_time(): void
    {
        Http::fake([
            self::BASE.'/deployment/dep-1' => Http::response([
                'id' => 'dep-1',
                'name' => 'process_1',
                'source' => 'Laravolt Camunda Package (test)',
                'deploymentTime' => '2026-10-11T07:10:11.000+0000',
                'tenantId' => null,
                'links' => [],
            ]),
        ]);

        $deployment = DeploymentClient::find('dep-1');

        $this->assertInstanceOf(Deployment::class, $deployment);
        $this->assertInstanceOf(Carbon::class, $deployment->deploymentTime);
    }

    public function test_external_task_fetch_and_lock(): void
    {
        Http::fake([
            self::BASE.'/external-task/fetchAndLock' => Http::response([[
                'activityId' => 'task_1',
                'activityInstanceId' => 'task_1:1',
                'errorMessage' => null,
                'errorDetails' => null,
                'executionId' => 'exec-1',
                'id' => 'ext-1',
                'lockExpirationTime' => '2026-10-11T07:10:11.000+0000',
                'createTime' => '2026-10-11T07:00:00.000+0000',
                'processDefinitionId' => 'pd-1',
                'processDefinitionKey' => 'process_1',
                'processDefinitionVersionTag' => null,
                'processInstanceId' => 'pi-1',
                'retries' => null,
                'suspended' => false,
                'workerId' => 'worker-1',
                'topicName' => 'topic_1',
                'tenantId' => null,
                'variables' => [],
                'priority' => 0,
                'businessKey' => null,
                'extensionProperties' => [],
            ]]),
        ]);

        $tasks = ExternalTaskClient::fetchAndLock('worker-1', [['topicName' => 'topic_1', 'lockDuration' => 1000]]);

        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(ExternalTask::class, $tasks[0]);
        $this->assertInstanceOf(Carbon::class, $tasks[0]->lockExpirationTime);
    }

    private function processDefinition(): array
    {
        return [
            'id' => 'process_1:1:pd-1',
            'key' => 'process_1',
            'category' => 'http://bpmn.io/schema/bpmn',
            'description' => null,
            'name' => 'Process 1',
            'version' => 1,
            'resource' => 'sample.bpmn',
            'deploymentId' => 'dep-1',
            'diagram' => null,
            'suspended' => false,
            'tenantId' => null,
            'versionTag' => null,
            'historyTimeToLive' => null,
            'startableInTasklist' => true,
        ];
    }

    private function processInstance(): array
    {
        return [
            'links' => [],
            'id' => 'pi-1',
            'definitionId' => 'process_1:1:pd-1',
            'definitionKey' => 'process_1',
            'businessKey' => 'bk-1',
            'caseInstanceId' => null,
            'ended' => false,
            'suspended' => false,
            'tenantId' => null,
            'variables' => [],
        ];
    }
}
