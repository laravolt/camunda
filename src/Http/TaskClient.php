<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Task;
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

    public static function submit(string $id, array $variables): bool
    {
        $payload['variables'] = self::formatVariables($variables);

        $response = self::make()->post("task/$id/submit-form", $payload);

        if ($response->status() === 204) {
            return true;
        }

        throw new CamundaException($response->json('message'), $response->status());
    }
}
