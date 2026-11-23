<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;

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
        $response = self::make()->get('process-instance/'.self::segment($id));

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
        $variables = self::make()->get('process-instance/'.self::segment($id).'/variables', ['deserializeValues' => false])->json();

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

    /**
     * Suspend a process instance: its tasks and jobs stay where they are until it is activated.
     */
    public static function suspend(string $id): bool
    {
        return self::setSuspended($id, true);
    }

    public static function activate(string $id): bool
    {
        return self::setSuspended($id, false);
    }

    /**
     * Update and delete process variables in one call (`POST /process-instance/{id}/variables`).
     *
     * @param  array<string, array{value: mixed, type?: string, valueInfo?: array}>  $modifications
     * @param  string[]  $deletions
     */
    public static function setVariables(string $id, array $modifications = [], array $deletions = []): bool
    {
        if ($modifications === [] && $deletions === []) {
            throw new InvalidArgumentException('Nothing to modify: pass modifications or deletions');
        }

        $payload = [];
        if ($modifications !== []) {
            self::guardVariables($modifications);
            $payload['modifications'] = $modifications;
        }
        if ($deletions !== []) {
            $payload['deletions'] = array_values($deletions);
        }

        $url = 'process-instance/'.self::segment($id).'/variables';
        $response = self::make()->post($url, $payload);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }
        if ($response->status() !== 204) {
            throw (new UnexpectedResponseException)->for($url, $payload, (array) $response->json());
        }

        return true;
    }

    protected static function setSuspended(string $id, bool $suspended): bool
    {
        $url = 'process-instance/'.self::segment($id).'/suspended';
        $response = self::make()->put($url, ['suspended' => $suspended]);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }
        if ($response->status() !== 204) {
            throw (new UnexpectedResponseException)->for($url, ['suspended' => $suspended], (array) $response->json());
        }

        return true;
    }

    public static function delete(string $id): bool
    {
        return self::make()->delete('process-instance/'.self::segment($id))->status() === 204;
    }
}
