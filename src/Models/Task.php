<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Models;

class Task extends CamundaModel
{
    public $name;
    public $assignee;
    public $date;
    public $due;
    public $followUp;
    public $delegationState;
    public $description;
    public $executionId;
    public $owner;
    public $parentTaskId;
    public $priority;
    public $processDefinitionId;
    public $processInstanceId;
    public $taskDefinitionId;
    public $taskDefinitionKey;
    public $caseExecutionId;
    public $caseInstanceId;
    public $caseDefinitionId;
    public $suspended;
    public $formKey;
    public $tenantId;
    public $created;
    protected $processInstance;

    protected string $endpoint = 'task';

    public function processInstance(): ProcessInstance
    {
        if (!$this->processInstance) {
            $this->processInstance = (new ProcessInstance($this->processInstanceId))->fetch();
        }

        return $this->processInstance;
    }

    public function setProcessInstance(ProcessInstance $processInstance)
    {
        $this->processInstance = $processInstance;
    }

    public function setVariables(array $modifications, array $deletions = [])
    {
        $modifications = $this->formatVariables($modifications);
        $deletions = $this->formatVariables($deletions);

        $this->post('variables', [
            'modifications' => $modifications,
            'deletions' => $deletions,
        ], true);
    }

    public function submit(array $data)
    {
        $payload = ['variables' => $this->formatVariables($data)];
        $response = self::request()->post("task/{$this->id}/submit-form", $payload);

        return $response->status() === 204;
    }
}
