<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\Task;
use Laravolt\Camunda\Dto\TaskHistory;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessInstanceClient extends CamundaClient
{
    public static function get(array $parameters = []): array
    {
        $instances = [];
        foreach (self::make()->get('process-instance', $parameters)->json() as $res) {
            $instances[] = new ProcessInstance($res);
        }

        return $instances;
    }

    public static function find(string $id): ProcessInstance
    {
        $response = self::make()->get("process-instance/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new ProcessInstance($response->json());
    }

    /**
     * @param  string  $processInstanceId
     *
     * @return Task[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function tasks(string $processInstanceId): array
    {
        $response = self::make()->get("task/?processInstanceId=$processInstanceId");

        $data = [];
        if ($response->successful()) {
            foreach ($response->json() as $task) {
                $data[] = new Task($task);
            }
        }

        return $data;
    }

    /**
     * @param  string  $processInstanceId
     *
     * @return TaskHistory[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function completedTasks(string $processInstanceId): array
    {
        $response = self::make()
            ->get(
                "history/task",
                [
                    'processInstanceId' => $processInstanceId,
                    'finished' => true,
                ]
            );

        if ($response->successful()) {
            $data = collect();
            foreach ($response->json() as $task) {
                $data->push(new TaskHistory($task));
            }

            return $data->sortBy('endTime')->toArray();
        }

        return [];
    }

    public static function variables(string $processInstanceId): array
    {
        $variables = self::make()->get("process-instance/$processInstanceId/variables")->json();

        return collect($variables)->mapWithKeys(
            fn ($data, $name) => [$name => new Variable(name: $name, value: $data['value'], type: $data['type'])]
        )->toArray();
    }

    public static function delete(string $id): bool
    {
        return self::make()->delete("process-instance/$id")->status() === 204;
    }
}
