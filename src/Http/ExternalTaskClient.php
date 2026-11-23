<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Exceptions\CamundaException;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;

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

    public static function find(string $id): ExternalTask
    {
        $response = self::make()->get('external-task/'.self::segment($id));

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return ExternalTask::from($response->json());
    }

    /**
     * @param string $processInstanceId
     *
     * @return ExternalTask[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function getByProcessInstanceId(string $id): array
    {
        $response = self::make()->get('external-task', ['processInstanceId' => $id]);

        $data = [];
        if ($response->successful()) {
            foreach ($response->json() as $task) {
                $data[] = ExternalTask::from($task);
            }
        }

        return $data;
    }

    /**
     * @return ExternalTask[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function getTaskLocked(): array
    {
        $response = self::make()->get("external-task?locked=true");

        $data = [];
        if ($response->successful()) {
            foreach ($response->json() as $task) {
                $data[] = ExternalTask::from($task);
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
                $data[] = ExternalTask::from($raw);
            }

            return $data;
        }

        throw new CamundaException($response->json('message') ?? $response->body());
    }

    public static function complete(
        string $id,
        string $workerId,
        array $variables = [],
        array $localVariables = []
    ): bool {
        self::guardVariables($variables);
        self::guardVariables($localVariables);
        $payload = compact('workerId');
        if ($variables) {
            $payload['variables'] = $variables;
        }
        if ($localVariables) {
            $payload['localVariables'] = $localVariables;
        }
        $url = 'external-task/'.self::segment($id).'/complete';
        $response = self::make()->post($url, $payload);
        $isSuccessful = $response->status() === 204;

        if (! $isSuccessful) {
            throw (new UnexpectedResponseException)->for($url, $payload, $response->json());
        }

        return $isSuccessful;
    }


    public static function fail(
        string $id,
        string $workerId,
        string $errorMessage = "Does not compute",
        int    $retryTimeout = 60000,
        int    $retries = 0,
    ): bool
    {
        // retries 0 (the engine's default) opens a failedExternalTask incident.
        $payload = compact('workerId', 'errorMessage', 'retryTimeout', 'retries');
        $url = 'external-task/'.self::segment($id).'/failure';
        $response = self::make()->post($url, $payload);
        $isSuccessful = $response->status() === 204;

        if (!$isSuccessful) {
            throw (new UnexpectedResponseException)->for($url, $payload, $response->json());
        }

        return $isSuccessful;
    }

    /**
     * Set the retries of an external task. Retries above zero resolve the incident
     * a task gets when it fails with no retries left.
     */
    public static function setRetries(string $id, int $retries): bool
    {
        if ($retries < 0) {
            throw new InvalidArgumentException('Retries cannot be negative');
        }

        $url = 'external-task/'.self::segment($id).'/retries';
        $response = self::make()->put($url, ['retries' => $retries]);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }
        if ($response->status() !== 204) {
            throw (new UnexpectedResponseException)->for($url, ['retries' => $retries], (array) $response->json());
        }

        return true;
    }

    public static function lock(string $id, string $workerId, int $duration): bool
    {
        $response =
            self::make()->post('external-task/'.self::segment($id).'/lock', ['workerId' => $workerId, 'lockDuration' => $duration]);

        return $response->status() === 204;
    }

    public static function unlock(string $id): bool
    {
        $response = self::make()->post('external-task/'.self::segment($id).'/unlock');

        return $response->status() === 204;
    }
}
