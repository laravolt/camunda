<?php

namespace Laravolt\Camunda;

use Laravolt\Camunda\Commands\ConsumeExternalTaskCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ConsumeExternalTaskCommand::class]);
        }
    }
}
