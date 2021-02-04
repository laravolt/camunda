<?php

namespace Laravolt\Camunda\Tests\Models;

use Laravolt\Camunda\Exceptions\ParseException;
use Laravolt\Camunda\Models\Deployment;
use Laravolt\Camunda\Tests\TestCase;

class DeploymentTest extends TestCase
{
    /** @test */
    public function it_can_deploy_bpmn()
    {
        $files = __DIR__.'/../../resources/sample.bpmn';
        $deployment = Deployment::create('test', $files);

        $this->assertInstanceOf(Deployment::class, $deployment);
    }

    /** @test */
    public function it_cannot_deploy_invalid_bpmn()
    {
        $this->expectException(ParseException::class);
        $files = __DIR__.'/../../resources/invalid.bpmn';
        Deployment::create('test', $files);
    }
}
