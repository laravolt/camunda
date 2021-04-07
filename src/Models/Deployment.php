<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Models;

use Carbon\Carbon;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\ParseException;

class Deployment extends CamundaModel
{
    public ?string $name;

    public ?string $source;

    public Carbon $deploymentTime;

    public array $processDefinitions = [];

    public static function fromApiResponse(array $data): self
    {
        $processDefinitions = [];
        foreach ($data['deployedProcessDefinitions'] ?? [] as $deployedProcessDefinitions) {
            $processDefinitions[] = new ProcessDefinition($deployedProcessDefinitions);
        }

        return new self([
            'id' => $data['id'],
            'name' => $data['name'],
            'source' => $data['source'],
            'deploymentTime' => Carbon::parse($data['deploymentTime']),
            'processDefinitions' => $processDefinitions,
        ]);
    }

    public static function create(string $name, string|array $bpmnFiles): self
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

        $request = self::request()->withOptions(['multipart' => $multipart]);

        foreach ((array)$bpmnFiles as $bpmn) {
            $filename = pathinfo($bpmn)['basename'];
            $request->attach($filename, file_get_contents($bpmn), $filename);
        }

        $response = $request->post('deployment/create');

        if ($response->status() === 400) {
            throw new ParseException($response->json('message'));
        }

        return self::fromApiResponse($response->json());
    }

    public static function get(string $id): Deployment
    {
        $response = self::request()->get("deployment/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return self::fromApiResponse($response->json());

    }

    public static function getList(): array
    {
        $response = self::request()->get('deployment');
        $result = [];
        foreach ($response->json() as $data) {
            $result[] = self::fromApiResponse($data);
        }

        return $result;
    }

    public static function truncate(bool $cascade = false): void
    {
        $deployments = self::getList();
        foreach ($deployments as $deployment) {
            $deployment->delete($cascade);
        }
    }

    public function delete(bool $cascade = false): bool
    {
        $cascadeFlag = $cascade ? 'cascade=true' : '';
        $response = self::request()->delete("deployment/{$this->id}?".$cascadeFlag);

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return $response->status() === 204;
    }
}
