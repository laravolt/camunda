<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\ProcessInstanceHistory;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Spatie\DataTransferObject\DataTransferObject;

class ProcessInstanceHistoryClient extends CamundaClient
{
    /**
     * @param  array  $parameters
     *
     * @return array|ProcessInstanceHistory[]
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function get(array $parameters = []): array
    {
        $instances = [];
        foreach (self::make()->get('history/process-instance', $parameters)->json() as $res) {
            $instances[] = new ProcessInstanceHistory($res);
        }

        return $instances;
    }

    /**
     * @param  string  $id
     *
     * @return \Laravolt\Camunda\Dto\ProcessInstanceHistory
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function find(string $id): ProcessInstanceHistory
    {
        $response = self::make()->get("history/process-instance/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new ProcessInstanceHistory($response->json());
    }

    public static function variables(string $id): array
    {
        $variables = self::make()->get("history/variable-instance", ['processInstanceId' => $id])->json();

        return collect($variables)->mapWithKeys(
            fn ($data) => [$data['name'] => new Variable(name: $data['name'], value: $data['value'], type: $data['type'])]
        )->toArray();
    }
}
