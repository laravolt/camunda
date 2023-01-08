<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Exceptions\CamundaException;

class ExternalTaskClient extends CamundaClient
{
    protected static array $subscribers = [];

    public static function subscribers(): array
    {
        return self::$subscribers;
    }

    /**
     * @param string $topic
     * @param class-string|array $job
     */
    public static function subscribe(string $topic, string|array $job): void
    {
        self::$subscribers[$topic] = [
            'topicName' => $topic,
            'job' => is_string($job) ? $job : $job['job'] ?? null,
            'lockDuration' => $job['lockDuration'] ?? 600_000,
        ];
    }

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

    public static function complete(
        string $id,
        string $workerId,
        array $variables = [],
        array $localVariables = []
    ): bool {
        $payload = compact('workerId');
        if ($variables) {
            $payload['variables'] = $variables;
        }
        if ($localVariables) {
            $payload['localVariables'] = $localVariables;
        }
        $response = self::make()->post("external-task/$id/complete", $payload);

        return $response->status() === 204;
    }

    public static function unlock(string $id): bool
    {
        $response = self::make()->post("external-task/$id/unlock");

        return $response->status() === 204;
    }
}
