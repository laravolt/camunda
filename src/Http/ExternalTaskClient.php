<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Exceptions\CamundaException;

class ExternalTaskClient extends CamundaClient
{
    public static function fetchAndLock(string $workerId, array $topics, int $maxTasks = 10): array
    {
        $payload = [
            'workerId' => $workerId,
            'maxTasks' => $maxTasks,
            'topics' => $topics,
        ];
        $response = self::make()->post("external-task/fetchAndLock", $payload);

        if ($response->successful()) {
            $data = [];
            foreach ($response->json() as $raw) {
                $data[] = new ExternalTask($raw);
            }

            return $data;
        }

        throw new CamundaException($response->json('message'));
    }
}
