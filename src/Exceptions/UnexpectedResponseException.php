<?php

namespace Laravolt\Camunda\Exceptions;

class UnexpectedResponseException extends CamundaException
{
    protected string $url;
    protected array $payload;
    protected array $response;

    public static function for(string $url, array $payload, array $response)
    {
        $instance = new static('Error processing request', $response['code'] ?? 0);
        $instance->url = $url;
        $instance->payload = $payload;
        $instance->response = $response;

        return $instance;
    }

    public function context()
    {
        return [
            'url' => $this->url,
            'payload' => $this->payload,
            'response' => $this->response,
        ];
    }
}
