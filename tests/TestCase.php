<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Deployment;

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

    protected function deploySampleBpmn(): \Laravolt\Camunda\Dto\Deployment
    {
        $files = __DIR__.'/../resources/sample.bpmn';
        return Deployment::create('test', $files);
    }

    protected function undeploySampleBpmn()
    {
        Deployment::truncate(true);
    }
}
