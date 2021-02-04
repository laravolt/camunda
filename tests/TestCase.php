<?php

namespace Laravolt\Camunda\Tests;

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
}
