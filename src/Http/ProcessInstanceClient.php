<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Http;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;

class ProcessInstanceClient extends CamundaClient
{
    public static function find(string $id): self
    {
        $response = self::make()->get("process-instance/$id");

        if ($response->status() === 404) {
            throw new ObjectNotFoundException($response->json('message'));
        }

        return new \Laravolt\Camunda\Dto\ProcessInstance($response->json());
    }

    public function currentTask()
    {
        $tasks = $this->tasks();

        return $tasks[0] ?? null;
    }

    public static function tasks(string $processInstanceId): array
    {
        $tasks = self::make()->get("task/?processInstanceId=$processInstanceId")->json();

        $data = [];
        foreach ($tasks as $task) {
            $task = new Task($task);
            $task->setProcessInstance($this);
            $data[] = $task;
        }

        return $data;
    }

    public function completedTasks(string $processInstanceId)
    {
        $tasks = self::make()->get("history/task/?processInstanceId=$processInstanceId")->json();
        $data = [];
        foreach ($tasks as $task) {
            $task = new TaskHistory($task);
            $task->setProcessInstance($this);
            $data[] = $task;
        }

        return $data;
    }

    public function setVariable($key, $value, $type = 'String'): void
    {
        $this->put(
            'variables/'.$key,
            [
                'type' => $type,
                'value' => $value,
            ],
            true
        );
    }

    public function setVariables(array $modifications, array $deletions = []): void
    {
        $modifications = self::formatVariables($modifications);
        $deletions = self::formatVariables($deletions);

        $this->post(
            'variables',
            [
                'modifications' => $modifications,
                'deletions' => $deletions,
            ],
            true
        );
    }

    public function getInfo()
    {
        return $this->get('');
    }

    public function getVariable($key)
    {
        return $this->get('variables/'.$key);
    }

    public function getVariables()
    {
        return get_object_vars($this->get('variables'));
    }

    public function deleteProcessInstance()
    {
        return $this->delete('');
    }

    public function ended()
    {
        return $this->get('history/process-instance/?processInstanceId='.$this->id)[0]->state == 'COMPLETED';
    }

    public function getEndEventId()
    {
        return optional(
            Arr::first(
                $this->get('history/activity-instance/?processInstanceId='.$this->id.'&activityType=noneEndEvent')
            )
        )->activityId;
    }

    public function modify($data)
    {
        return $this->post('modification', $data, true);
    }

    public function getSubProcess()
    {
        $subProcess = $this->get('process-instance?superProcessInstance='.$this->id);
        $data = [];
        foreach ($subProcess as $sub) {
            $data[] = new self($sub->id, $sub);
        }

        return $data;
    }

    public function suspend()
    {
        $data = [
            'suspended' => true,
        ];

        return $this->put('suspended', $data, true);
    }

    /*
     * Undo process: cancel last task and move one step backward
     * See https://docs.camunda.org/manual/7.8/reference/rest/process-instance/post-modification/
     */
    public function undo()
    {
        $activities = $this->get(
            'history/activity-instance?processInstanceId='.$this->id
            .'&sortBy=startTime&sortOrder=desc&activityType=userTask'
        );

        $activities = collect($activities)
            ->reject(
                function ($item) {
                    return $item->canceled;
                }
            );

        $currenctActivity = $activities->shift();
        $previousActivity = $activities->shift();

        if (! $currenctActivity || ! $previousActivity) {
            throw new \DomainException(
                sprintf(
                    'Process instance %s tidak memiliki Activity Instance yang valid',
                    $this->id
                )
            );
        }

        $cancellation = new \stdClass();
        $cancellation->type = 'cancel';
        $cancellation->activityInstanceId = $currenctActivity->id;

        $moveBackward = new \stdClass();
        $moveBackward->type = 'startBeforeActivity';
        $moveBackward->activityId = $previousActivity->activityId;

        $payload = [
            'skipCustomListeners' => true,
            'skipIoMappings' => true,
            'instructions' => [$moveBackward, $cancellation],
        ];

        try {
            $this->post('modification', $payload, true);

            return [$previousActivity, $currenctActivity];
        } catch (ClientException $e) {
            $message = json_decode((string)$e->getResponse()->getBody())->message ?? $e->getMessage();

            throw new \Exception($message);
        }
    }

    public function moveTo(string $taskDefinitionKey)
    {
        $activities = $this->get(
            'history/activity-instance?processInstanceId='.$this->id
            .'&sortBy=startTime&sortOrder=asc&activityType=userTask'
        );

        $activities = collect($activities)
            ->reject(
                function ($item) {
                    return $item->canceled;
                }
            );

        $targetActivity = $activities->firstWhere('activityId', $taskDefinitionKey);
        $targetActivityIndex = $activities->search(
            function ($item) use ($targetActivity) {
                return $item->id == $targetActivity->id;
            }
        );

        if (! $targetActivity) {
            throw new \DomainException(sprintf('Invalid $taskDefinitionKey: %s', $taskDefinitionKey));
        }

        $canceledActivities = $activities->splice($targetActivityIndex + 1);
        $toBeCanceled = $canceledActivities->where('endTime', null);

        $instructions = [];

        foreach ($toBeCanceled as $activity) {
            $cancellation = new \stdClass();
            $cancellation->type = 'cancel';
            $cancellation->activityInstanceId = $activity->id;
            $instructions[] = $cancellation;
        }

        $jumpToTargetActivity = new \stdClass();
        $jumpToTargetActivity->type = 'startBeforeActivity';
        $jumpToTargetActivity->activityId = $targetActivity->activityId;
        $instructions[] = $jumpToTargetActivity;

        $payload = [
            'skipCustomListeners' => true,
            'skipIoMappings' => true,
            'instructions' => $instructions,
        ];

        try {
            $this->post('modification', $payload, true);

            return $canceledActivities->prepend($targetActivity);
        } catch (ClientException $e) {
            $message = json_decode((string)$e->getResponse()->getBody())->message ?? $e->getMessage();

            throw new \Exception($message);
        }
    }
}
