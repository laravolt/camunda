<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use Laravolt\Camunda\Dto\Deployment;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\ParseException;

class DeploymentClient extends CamundaClient
{
    public static function create(string $name, string|array $bpmnFiles): Deployment
    {
        $multipart = [
            ['name' => 'deployment-name', 'contents' => $name],
            ['name' => 'deployment-source', 'contents' => sprintf('%s (%s)', config('app.name'), config('app.env'))],
            ['name' => 'enable-duplicate-filtering', 'contents' => 'true'],
        ];

        if (config('services.camunda.tenant_id')) {
            $multipart[] = [
                'name' => 'tenant-id',
                'contents' => config('services.camunda.tenant_id'),
            ];
        }

        $request = self::make()->asMultipart();
        foreach ((array) $bpmnFiles as $bpmn) {
            $filename = pathinfo($bpmn)['basename'];
            $request->attach($filename, file_get_contents($bpmn), $filename);
        }

        $response = $request->post('deployment/create', $multipart);

        if ($response->status() === 400) {
            throw new ParseException($response->json('message'));
        }

        return new Deployment($response->json());
    }

    public static function find(string $id): Deployment
    {
        $response = self::make()->get("deployment/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new Deployment($response->json());
    }

    public static function get(array $parameters = []): array
    {
        $response = self::make()->get('deployment', $parameters);
        $result = [];
        foreach ($response->json() as $data) {
            $result[] = new Deployment($data);
        }

        return $result;
    }

    public static function truncate(bool $cascade = false): void
    {
        $deployments = self::get();
        foreach ($deployments as $deployment) {
            self::delete($deployment->id, $cascade);
        }
    }

    public static function delete(string $id, bool $cascade = false): bool
    {
        $cascadeFlag = $cascade ? 'cascade=true' : '';
        $response = self::make()->delete("deployment/{$id}?".$cascadeFlag);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return $response->status() === 204;
    }
}
