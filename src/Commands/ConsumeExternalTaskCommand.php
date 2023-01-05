<?php

namespace Laravolt\Camunda\Commands;

use Illuminate\Console\Command;

class ConsumeExternalTaskCommand extends Command
{
    protected $signature = 'camunda:consume-external-task {--topic=*}';

    protected $description = 'Consume Camunda external task by topic {--topic : A Camunda external task topic name to be fetched}';

    public function handle()
    {

    }
}
