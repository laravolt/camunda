<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Laravolt\Camunda\Exceptions\ParseException;

class Deployment extends CamundaModel
{
    public string $name;
    public string $source;
    public Carbon $deploymentTime;

    public static function fromApiResponse(array $data): self
    {
        return new self([
            'id' => $data['id'],
            'name' => $data['name'],
            'source' => $data['source'],
            'deploymentTime' => Carbon::parse($data['deploymentTime']),
        ]);
    }

    public static function create($name, $bpmnFiles): self
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

        $request = Http::withOptions(['multipart' => $multipart]);

        foreach ((array)$bpmnFiles as $bpmn) {
            $filename = pathinfo($bpmn)['basename'];
            $request->attach($filename, file_get_contents($bpmn), $filename);
        }

        $response = $request->post(config('services.camunda.url').'deployment/create');

        if ($response->status() === 400) {
            throw new ParseException($response->json('message'));
        }

        return self::fromApiResponse($response->json());
    }
}
