<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\Deployment;
use Laravolt\Camunda\Http\DeploymentClient;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Runner\ErrorHandler;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel's HandleExceptions bootstrapper routes E_DEPRECATED / E_USER_DEPRECATED to the
        // "deprecations" log channel while testing, so PHPUnit never sees them and
        // failOnDeprecation would silently pass. Hand PHP errors back to PHPUnit's handler so the
        // <source> filter in phpunit.dist.xml decides what fails. Laravel restores all handlers
        // in tearDown() (HandleExceptions::flushHandlersState()).
        set_error_handler(ErrorHandler::instance());
    }

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
        $app['config']->set('services.camunda.user', env('CAMUNDA_USER'));
        $app['config']->set('services.camunda.password', env('CAMUNDA_PASSWORD'));
    }

    protected function deploySampleBpmn(): Deployment
    {
        $files = [__DIR__.'/../resources/sample.bpmn'];


        return DeploymentClient::create('process_1', $files);
    }

    protected function truncateDeployment(bool $cascade = true): void
    {
        // tearDown() still runs when setUp() skipped the test; nothing to clean up then.
        if (! IntegrationTestCase::camundaConfigured()) {
            return;
        }

        DeploymentClient::truncate($cascade);
    }
}
