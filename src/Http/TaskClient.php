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

    public static function submit(string $id, array $variables, bool $withVariablesInReturn = true): bool|array
    {
        $response = self::make()->post(
            "task/$id/submit-form",
            compact('variables', 'withVariablesInReturn')
        );

        if ($response->status() === 204) {
            return true;
        }

        if ($response->status() === 200) {
            return (new VariablesCaster('array', Variable::class))->cast($response->json());
        }

        throw new CamundaException($response->body(), $response->status());
    }
}
