
# laravolt/camunda
Convenience Laravel HTTP client wrapper to interact with Camunda REST API.

## Requirements

| laravolt/camunda | PHP       | Laravel     | spatie/laravel-data |
|------------------|-----------|-------------|---------------------|
| 3.x              | 8.3 – 8.5 | 12.x, 13.x  | ^4.23               |
| 2.x              | ≥ 8.1     | 10.x¹       | ^3.0 \| ^4.0        |

¹ 2.x does not declare an `illuminate/*` constraint; its test suite only ran against Laravel 10.

The client talks to the Camunda 7 REST API. CI runs the integration tests against Camunda 7.24 (the last
Community Edition release) and [Operaton](https://operaton.org) 2.2, its open-source continuation with the
same API under `/engine-rest`.

Upgrading from 2.x? See [UPGRADE.md](UPGRADE.md).

## Installation
`composer require laravolt/camunda`

## Configuration

Prepare your `.env`:

```dotenv
CAMUNDA_URL=http://localhost:8080/engine-rest

#optional
CAMUNDA_TENANT_ID=
CAMUNDA_USER=
CAMUNDA_PASSWORD=
```

Add following entries to `config/services.php`:
```php
'camunda' => [
    'url' => env('CAMUNDA_URL', 'https://localhost:8080/engine-rest'),
    'user' => env('CAMUNDA_USER', 'demo'),
    'password' => env('CAMUNDA_PASSWORD', 'demo'),
    'tenant_id' => env('CAMUNDA_TENANT_ID', ''),
],
```


## Usage

### Process Definition
```php
use Laravolt\Camunda\Http\ProcessDefinitionClient;

$variables = ['title' => ['value' => 'Sample Title', 'type' => 'string']];

// Start new process instance
$instance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);

// Start new process instance with some business key
$instance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables, businessKey: 'somekey');


// Get BPMN definition in XML format
ProcessDefinitionClient::xml(key: 'process_1');
ProcessDefinitionClient::xml(id: 'process_1:xxxx');

// Get all definition
ProcessDefinitionClient::get();

// Get definitions based on some parameters
$params = ['latestVersion' => true];
ProcessDefinitionClient::get($params);
```

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/process-definition/




### Process Instance
```php
use Laravolt\Camunda\Http\ProcessInstanceClient;

// Find by ID
$processInstance = ProcessInstanceClient::find(id: 'some-id');

// Get all instances
ProcessInstanceClient::get();

// Get instances based on some parameters
$params = ['businessKeyLike' => 'somekey'];
ProcessInstanceClient::get($params);

ProcessInstanceClient::variables(id: 'some-id');
ProcessInstanceClient::delete(id: 'some-id');

// Pause and resume an instance (3.1)
ProcessInstanceClient::suspend('some-id');
ProcessInstanceClient::activate('some-id');

// Update and delete variables in one call (3.1)
ProcessInstanceClient::setVariables('some-id',
    modifications: ['status' => ['value' => 'checked', 'type' => 'String']],
    deletions: ['draft'],
);
```

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/process-instance/


### Message and Signal Events

```php
use Laravolt\Camunda\Http\MessageEventClient;
use Laravolt\Camunda\Http\SignalEventClient;

// Start a process instance through its message start event; returns the new ProcessInstance.
MessageEventClient::start(messageName: 'order_placed', businessKey: 'order-42', variables: [
    'amount' => ['value' => 150, 'type' => 'Integer'],
]);

// Deliver a message to the instance waiting for it (intermediate catch event or receive task) (3.1).
// Narrow the target with businessKey, processInstanceId or correlationKeys; all: true delivers to every match.
$results = MessageEventClient::correlate(
    messageName: 'payment_received',
    businessKey: 'invoice-7',
    processVariables: ['paid' => ['value' => true, 'type' => 'Boolean']],
);
$results[0]->resultType; // 'Execution' or 'ProcessDefinition'

// Broadcast a signal to every instance and definition that waits for it (3.1).
SignalEventClient::throw('release_all', variables: ['by' => ['value' => 'ops', 'type' => 'String']]);
```

`correlate()` throws `ObjectNotFoundException` when nothing waits for the message.

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/message/ and https://docs.camunda.org/manual/latest/reference/rest/signal/

### Task
```php
use Laravolt\Camunda\Http\TaskClient;

$task = TaskClient::find(id: 'task-id');
$tasks = TaskClient::getByProcessInstanceId(id: 'process-instance-id');
$tasks = TaskClient::getByProcessInstanceIds(ids: 'arrayof-process-instance-ids');
TaskClient::submit(id: 'task-id', variables: ['name' => ['value' => 'Foo', 'type' => 'String']]); // will return true or false
$variables = TaskClient::submitAndReturnVariables(id: 'task-id', variables: ['name' => ['value' => 'Foo', 'type' => 'String']]) // will return array of variable

// Claim a Task
$tasks = TaskClient::claim($task_id,  $user_id);
// Unclaim a Task
$tasks = TaskClient::unclaim($task_id);
// Assign a Task
$tasks = TaskClient::assign($task_id,  $user_id);



```

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/task/

### External Task
```php
use Laravolt\Camunda\Http\ExternalTaskClient;

$topics = [
    ['topicName' => 'pdf', 'lockDuration' => 600_000]
];
$externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
foreach ($externalTasks as $externalTask) {
    // do something with $externalTask
    // Mark as complete after finished
    ExternalTaskClient::complete($externalTasks->id);
}

// Unlock some task
ExternalTaskClient::unlock($task->id)

// Get task locked
$externalTaskLocked = ExternalTaskClient::getTaskLocked();

// Report a failure; with retries 0 (the default) the engine opens an incident
ExternalTaskClient::fail($task->id, 'worker1', 'printer offline', retries: 0);
```

### Incidents (3.1)
```php
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\IncidentClient;

$incidents = IncidentClient::get(['processInstanceId' => $id]); // Incident[]
IncidentClient::count(['incidentType' => 'failedExternalTask']);
$incident = IncidentClient::find('incident-id');

// Give the failed external task new retries: the engine resolves its incident
ExternalTaskClient::setRetries($incident->configuration, 3);
```

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/external-task/

### Consume External Task
Create a new job to consume external task via `php artisan make:job <JobName>` and modify the skeleton:

```php
use Laravolt\Camunda\Dto\ExternalTask;
use Laravolt\Camunda\Http\ExternalTaskClient;

public function __construct(
    public string $workerId,
    public ExternalTask $task
) {
}

public function handle()
{
    // Do something with $this->task, e.g: get the variables and generate PDF
    $variables = \Laravolt\Camunda\Http\ProcessInstanceClient::variables($this->task->processDefinitionId);
    // PdfService::generate()

    // Complete the task
    $status = ExternalTaskClient::complete($this->task->id, $this->workerId);
}

```

Subscribe to some topic:
```php
// AppServiceProvider.php
use Laravolt\Camunda\Http\ExternalTaskClient;

public function boot()
{
    ExternalTaskClient::subscribe('pdf', GeneratePdf::class);
}
```

Register the scheduler:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('camunda:consume-external-task --workerId=worker1')->everyMinute();
}
```

If you need shorter pooling time (sub-minute frequency), please check [Laravel Short Schedule](https://github.com/spatie/laravel-short-schedule).

Reference:
- https://laravel.com/docs/master/scheduling
- https://laravel.com/docs/master/queues
- https://github.com/spatie/laravel-short-schedule

### Task History (Completed Task)

```php
use Laravolt\Camunda\Http\TaskHistoryClient;

$completedTask = TaskHistoryClient::find(id: 'task-id');
$completedTasks = TaskHistoryClient::getByProcessInstanceId(id: 'process-instance-id');
```

Camunda API reference: https://docs.camunda.org/manual/latest/reference/rest/history/task/



### Deployment

```php
use Laravolt\Camunda\Http\DeploymentClient;

// Deploy bpmn file(s)
DeploymentClient::create('test-deploy', '/path/to/file.bpmn');
DeploymentClient::create('test-deploy', ['/path/to/file1.bpmn', '/path/to/file2.bpmn']);

// Get deployment list
DeploymentClient::get();

// Find detailed info about some deployment
DeploymentClient::find($id);

// Truncate (delete all) deployments
$cascade = true;
DeploymentClient::truncate($cascade);

// Delete single deployment
DeploymentClient::delete(id: 'test-deploy', cascade: $cascade);

```



### Raw Endpoint

You can utilize `Laravolt\Camunda\CamundaClient` to call any Camunda REST endpoint.
```php
use Laravolt\Camunda\CamundaClient;

$response = CamundaClient::make()->get('version');
echo $response->status(); // 200
echo $response->object(); // sdtClass
echo $response->json(); // array, something like ["version" => "7.14.0"]
```
> `CamundaClient::make()` is a wrapper for [Laravel HTTP Client](https://laravel.com/docs/master/http-client) with base URL already set based on your Camunda services configuration. Take a look at the documentation for more information.

## Testing

```bash
composer test                # offline suite, integration tests excluded
CAMUNDA_URL=http://localhost:8080/engine-rest/ composer test:integration   # needs a running Camunda 7 REST API
```

Tests in the `integration` group are skipped automatically while `CAMUNDA_URL` is empty.
