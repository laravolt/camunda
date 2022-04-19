<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\ProcessInstance;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessInstanceClient extends CamundaClient
{
    public static function get(array $variables = []): array
    {
        /**
         *  $variables = [
         *       [
         *          'name' => 'varname',
         *          'operator' => "_eq_",
         *          'value' => 'varvalue',
         *       ],
         *   ];
         */

        /**
         * `operator` can only contain _eq_, _neq_, _gt_, _gte_, _lt_, _lte_
         * Check Camunda documentation for more information
         */

        $instances = [];

        if(count($variables) > 0) 
        {
            $reqstr = 'process-instance?variables=';
            foreach ($variables as $variable) {
                $reqstr .= $variable['name'].$variable['operator'].$variable['value'].',';
            }
            
        }
        else
        {
            $reqstr = 'process-instance';
        }

        foreach (self::make()->get($reqstr)->json() as $res) {
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

    public static function variables(string $id): array
    {
        $variables = self::make()->get("process-instance/$id/variables")->json();

        return collect($variables)->mapWithKeys(
            fn ($data, $name) => [$name => new Variable(name: $name, value: $data['value'], type: $data['type'])]
        )->toArray();
    }

    public static function delete(string $id): bool
    {
        return self::make()->delete("process-instance/$id")->status() === 204;
    }
}
