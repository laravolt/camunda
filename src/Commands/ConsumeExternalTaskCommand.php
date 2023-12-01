<?php

namespace Laravolt\Camunda\Commands;

use Illuminate\Console\Command;
use Laravolt\Camunda\Http\ExternalTaskClient;

class ConsumeExternalTaskCommand extends Command
{
    protected $signature = 'camunda:consume-external-task {--workerId=} {--topic=*} {--queue=default}';

    protected $description = '
        Consume Camunda external task by topic  
        {--workerId : A worker identifier} 
        {--topic : A Camunda external task topic name to be fetched}
        {--queue : A queue for job dispatching}
    ';

    public function handle()
    {
        $subscribers = ExternalTaskClient::subscribers();
        $topics = [];
        $summary = [];
        $workerId = $this->option('workerId');
        $queue = $this->option('queue');
        foreach ($subscribers as $topicName => $subscriber) {
            $topics[$topicName] = collect($subscriber)->only(['topicName', 'lockDuration'])->toArray();
            $summary[$topicName] = [$topicName, $subscriber['job'] ?? '-', 0];
        }
        $externalTasks = ExternalTaskClient::fetchAndLock($workerId, array_values($topics));

        /** @var \Laravolt\Camunda\Dto\ExternalTask $task */
        foreach ($externalTasks as $task) {
            $jobClass = $subscribers[$task->topicName]['job'] ?? false;
            if ($jobClass) {
                $jobClass::dispatch($workerId, $task)->onQueue($queue);
                $summary[$task->topicName][2]++;
            }
        }

        $this->table(['topic', 'Job', 'Job Dispatched'], $summary);
    }
}
