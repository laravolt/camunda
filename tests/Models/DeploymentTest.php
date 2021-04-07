<?php

namespace Laravolt\Camunda\Tests\Models;

use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\ParseException;
use Laravolt\Camunda\Models\Deployment;
use Laravolt\Camunda\Tests\TestCase;

class DeploymentTest extends TestCase
{
    protected function tearDown(): void
    {
        Deployment::truncate();
    }

    /** @test */
    public function it_can_deploy_bpmn(): void
    {
        $files = __DIR__.'/../../resources/sample.bpmn';
        $deployment = Deployment::create('test', $files);

        $this->assertEquals('test', $deployment->name);
    }

    /** @test */
    public function it_can_deploy_bpmn_with_tenant_id(): void
    {
        config()->set('services.camunda.tenant_id', 'sample-tenant');

        $files = __DIR__.'/../../resources/sample.bpmn';
        $deployment = Deployment::create('test', $files);

        $this->assertEquals('test', $deployment->name);
    }

    /** @test */
    public function it_can_deploy_multiple_bpmn(): void
    {
        $files = [
            __DIR__.'/../../resources/sample.bpmn',
            __DIR__.'/../../resources/sample2.bpmn',
        ];
        $deployment = Deployment::create('test', $files);

        $this->assertEquals('test', $deployment->name);
    }

    /** @test */
    public function it_cannot_deploy_invalid_bpmn(): void
    {
        $this->expectException(ParseException::class);

        $files = __DIR__.'/../../resources/invalid.bpmn';
        Deployment::create('test', $files);
    }

    /**
     * @test
     */
    public function it_can_get_deployment_by_id(): void
    {
        $files = __DIR__.'/../../resources/sample.bpmn';
        $deployment1 = Deployment::create('test', $files);

        $deployment2 = Deployment::get($deployment1->id);
        $this->assertEquals($deployment1->id, $deployment2->id);
    }

    /**
     * @test
     */
    public function it_cannot_get_deployment_by_invalid_id(): void
    {
        $this->expectException(ObjectNotFoundException::class);

        Deployment::get('some-invalid-id');
    }

    /**
     * @test
     */
    public function it_can_get_list_deployment(): void
    {
        Deployment::create('deployment1', __DIR__.'/../../resources/sample.bpmn');
        Deployment::create('deployment2', __DIR__.'/../../resources/sample2.bpmn');

        $deployments = Deployment::getList();
        $this->assertCount(2, $deployments);
    }

    /**
     * @test
     */
    public function it_can_delete_deployment(): void
    {
        $deployment = Deployment::create('deployment1', __DIR__.'/../../resources/sample.bpmn');
        $deleted = $deployment->delete();

        $this->assertTrue($deleted);
    }

    /**
     * @test
     */
    public function it_cannot_delete_invalid_deployment(): void
    {
        $this->expectException(ObjectNotFoundException::class);

        $deployment = new Deployment(
            id: 'invalid-id',
            name: 'test',
            deploymentTime: now(),
            processDefinitions: []
        );
        $deployment->delete();
    }

    /**
     * @test
     */
    public function it_can_truncate_deployment(): void
    {
        Deployment::create('deployment1', __DIR__.'/../../resources/sample.bpmn');
        Deployment::create('deployment2', __DIR__.'/../../resources/sample2.bpmn');

        Deployment::truncate();

        $deployments = Deployment::getList();
        $this->assertCount(0, $deployments);
    }
}
