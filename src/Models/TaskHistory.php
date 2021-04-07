<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Models;

class TaskHistory extends Task
{
    public $processDefinitionKey;
    public $caseDefinitionKey;
    public $activityInstanceId;
    public $deleteReason;
    public $startTime;
    public $endTime;
    public $duration;
    public $removalTime;
    public $rootProcessInstanceId;
}
