<?php

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Tenant;
use Laravolt\Camunda\Exceptions\CamundaException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class TenantClient extends CamundaClient
{
    public static function find(string $id): Tenant
    {
        $response = self::make()->get("tenant/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new Tenant($response->json());
    }

    public static function get(array $parameters = []): array
    {
        $response = self::make()->get('tenant', $parameters);
        $result = [];
        foreach ($response->json() as $data) {
            $result[] = new Tenant($data);
        }

        return $result;
    }

    public static function create(string $id, string $name): bool
    {
        $response = self::make()->post(
            "tenant/create",
            compact('id', 'name')
        );

        if ($response->status() === 204) {
            return true;
        }

        throw new CamundaException($response->body(), $response->status());
    }

    public static function delete(string $id): bool
    {
        $response = self::make()->delete("tenant/{$id}");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return $response->status() === 204;
    }

    public static function truncate(): void
    {
        foreach (self::get() as $tenant) {
            self::delete($tenant->id);
        }
    }
}
