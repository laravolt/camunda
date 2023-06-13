<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Exceptions\CamundaException;
use UnexpectedValueException;

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

    /**
     * @param string $processInstanceId
     *
     * @return ExternalTask[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function getByProcessInstanceId(string $id): array
    {
        $response = self::make()->get("external-task?processInstanceId=$id");

        $data = [];
        if ($response->successful()) {
            foreach ($response->json() as $task) {
                $data[] = new ExternalTask($task);
            }
        }

        return $data;
    }

    /**
     * @param string $workerId
     * @param array $topics
     * @param int $maxTasks
     *
     * @return ExternalTask[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
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
        $isSuccessful = $response->status() === 204;

        if (! $isSuccessful) {
            $context = print_r(['response' => $response->json(), 'payload' => $payload], true);
            throw new UnexpectedValueException("Error processing request. Context: ". $context, $response->status());
        }

        return $isSuccessful;
    }

    public static function unlock(string $id): bool
    {
        $response = self::make()->post("external-task/$id/unlock");

        return $response->status() === 204;
    }
}
