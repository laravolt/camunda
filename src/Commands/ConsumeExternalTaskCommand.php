<?php

namespace Laravolt\Camunda\Commands;

use Illuminate\Console\Command;
use Laravolt\Camunda\Http\ExternalTaskClient;

class ConsumeExternalTaskCommand extends Command
{
    protected $signature = 'camunda:consume-external-task {--workerId=} {--topic=*}';

    protected $description = '
        Consume Camunda external task by topic  
        {--workerId : A worker identifier} 
        {--topic : A Camunda external task topic name to be fetched}
    ';

    public function handle()
    {
        $subscribers = ExternalTaskClient::subscribers();
        $topics = [];
        $summary = [];
        $workerId = $this->option('workerId');
        foreach ($subscribers as $topicName => $subscriber) {
            $topics[$topicName] = collect($subscriber)->only(['topicName', 'lockDuration'])->toArray();
            $summary[$topicName] = [$topicName, $subscriber['job'] ?? '-', 0];
        }
        $externalTasks = ExternalTaskClient::fetchAndLock($workerId, array_values($topics));

        /** @var \Laravolt\Camunda\Dto\ExternalTask $task */
        foreach ($externalTasks as $task) {
            $jobClass = $subscribers[$task->topicName]['job'] ?? false;
            if ($jobClass) {
                $jobClass::dispatch($workerId, $task);
                $summary[$task->topicName][2]++;
            }
        }

        $this->table(['topic', 'Job', 'Job Dispatched'], $summary);
    }
}
