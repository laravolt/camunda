<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\Deployment;
use Laravolt\Camunda\Http\DeploymentClient;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.name', env('APP_NAME'));
        $app['config']->set('app.env', env('APP_ENV'));
        $app['config']->set('services.camunda.url', env('CAMUNDA_URL'));
    }

    protected function deploySampleBpmn(): Deployment
    {
        $files = [__DIR__.'/../resources/sample.bpmn'];

        return DeploymentClient::create('test', $files);
    }

    protected function truncateDeployment(): void
    {
        DeploymentClient::truncate(true);
    }
}
