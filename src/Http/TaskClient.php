<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Casters\VariablesCaster;
use Laravolt\Camunda\Dto\Task;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\CamundaException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class TaskClient extends CamundaClient
{
    public static function find(string $id): Task
    {
        $response = self::make()->get("task/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new Task($response->json());
    }

    /**
     * @param  string  $processInstanceId
     *
     * @return Task[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function getByProcessInstanceId(string $id): array
    {
        $response = self::make()->get("task?processInstanceId=$id");

        $data = [];
        if ($response->successful()) {
            foreach ($response->json() as $task) {
                $data[] = new Task($task);
            }
        }

        return $data;
    }

    public static function submit(string $id, array $variables): bool
    {
        $response = self::make()->post(
            "task/$id/submit-form",
            compact('variables')
        );

        if ($response->status() === 204) {
            return true;
        }

        throw new CamundaException($response->body(), $response->status());
    }

    public static function submitAndReturnVariables(string $id, array $variables): array
    {
        $response = self::make()->post(
            "task/$id/submit-form",
            ['variables' => $variables, 'withVariablesInReturn' => true]
        );

        if ($response->status() === 200) {
            return (new VariablesCaster('array', Variable::class))->cast($response->json());
        }

        throw new CamundaException($response->body(), $response->status());
    }
}
