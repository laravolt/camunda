
# laravolt/camunda

## Installation
`composer require laravolt/camunda`

## Setup .env
Add following config to `config/services.php`:
```php
'camunda' => [
    'url' => env('CAMUNDA_URL', 'https://localhost:8080/engine-rest'),
    'user' => env('CAMUNDA_USER', 'demo'),
    'password' => env('CAMUNDA_PASSWORD', 'demo'),
    'tenant_id' => env('CAMUNDA_TENANT_ID', ''),
],
```

And of course prepare your `.env`:
```
CAMUNDA_URL=http://localhost:8080/engine-rest
#optional
CAMUNDA_TENANT_ID=
CAMUNDA_USER=
CAMUNDA_PASSWORD=
```

## Usage

### Process Definition
@TODO

### Process Instance
@TODO

### Process Instance History
@TODO

### Task
@TODO

### Task Hisoty
@TODO

### Deployment
```php
use Laravolt\Camunda\Models\Deployment;

// Deploy bpmn file(s)
Deployment::create('test-deploy', '/path/to/file.bpmn');
Deployment::create('test-deploy', ['/path/to/file1.bpmn', '/path/to/file2.bpmn']);

// Get deployment list
Deployment::getList();

// Get deployment detail
Deployment::get($id);

// Truncate (delete all) deployments
$cascade = true; // or false
Deployment::truncate($cascade);

// Delete single deployment
$deployment = Deployment::create('test-deploy', '/path/to/file.bpmn');
$cascade = true; // or false
$deployment->delete($cascade);
```
