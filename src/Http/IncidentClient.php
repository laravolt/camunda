<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Incident;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;

class IncidentClient extends CamundaClient
{
    /**
     * Open incidents matching the query parameters of `GET /incident`,
     * for example `['processInstanceId' => $id]` or `['incidentType' => 'failedExternalTask']`.
     *
     * @param  array<string, scalar>  $parameters
     * @return Incident[]
     */
    public static function get(array $parameters = []): array
    {
        $response = self::make()->get('incident', $parameters);

        if (! $response->successful()) {
            throw (new UnexpectedResponseException)->for('incident', $parameters, (array) $response->json());
        }

        return array_map(fn (array $incident) => Incident::from($incident), $response->json());
    }

    public static function find(string $id): Incident
    {
        $response = self::make()->get('incident/'.self::segment($id));

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        if (! $response->successful()) {
            throw (new UnexpectedResponseException)->for('incident/'.$id, [], (array) $response->json());
        }

        return Incident::from($response->json());
    }

    /**
     * Number of open incidents matching the query parameters.
     *
     * @param  array<string, scalar>  $parameters
     */
    public static function count(array $parameters = []): int
    {
        return (int) self::make()->get('incident/count', $parameters)->json('count');
    }
}
