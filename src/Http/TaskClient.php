<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Task;
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
}
