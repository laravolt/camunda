<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessInstanceClient extends CamundaClient
{
    public static function get(array $parameters = []): array
    {
        $instances = [];
        if (! $parameters) {
            $res = self::make()->get('process-instance');
        } else {
            $res = self::make()->post('process-instance', $parameters);
        }
        foreach ($res->json() as $res) {
            $instances[] = ProcessInstance::from($res);
        }

        return $instances;
    }

    /**
     * Get process instances by variables.
     *
     * @param array $variables
     * $variables format:
     *   $variables = [
     *        [
     *           'name' => 'varname',
     *           'operator' => "eq",
     *           'value' => 'varvalue',
     *        ],
     *    ];
     *  `operator` can only contain `eq`, `neq`, `gt`, `gte`, `lt`, `lte`.
     *
     * @return ProcessInstance[]
     */
    public static function getByVariables(array $variables = []): array
    {
        $instances = [];

        if (! $variables) {
            $res = self::make()->get('process-instance');
        } else {
            $res = self::make()->post('process-instance', [
                'variables' => $variables,

            ]);
        }
        foreach ($res->json() as $res) {
            $instances[] = ProcessInstance::from($res);
        }

        return $instances;
    }

    public static function find(string $id): ProcessInstance
    {
        $response = self::make()->get("process-instance/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return ProcessInstance::from($response->json());
    }

    public static function findByBusniessKey(string $businessKey): ProcessInstance
    {
        $response = self::make()->post("process-instance", [
            'businessKey' => $businessKey,
        ]);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        $data = $response->json();

        if (count($data) == 0) {
            throw new ObjectNotFoundException("Process Instance Not Found");
        }

        return ProcessInstance::from($data[count($data) - 1]);
    }

    public static function variables(string $id): array
    {
        $variables = self::make()->get("process-instance/$id/variables", ['deserializeValues' => false])->json();

        $temp = collect($variables)->mapWithKeys(
            fn ($data, $name) => [
                $name => new Variable(
                    name: $name,
                    type: $data['type'],
                    value: $data['value'],
                    valueInfo: $data['valueInfo'] ?? []
                ),
            ]
        );

        $result = [];
        foreach ($temp as $name => $value) {
            $result[$name] = $value;
        }

        return $result;
    }

    public static function delete(string $id): bool
    {
        return self::make()->delete("process-instance/$id")->status() === 204;
    }
}
