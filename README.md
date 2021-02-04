
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
@TODO
