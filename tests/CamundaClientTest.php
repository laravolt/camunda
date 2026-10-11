<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Http\CamundaClient;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class CamundaClientTest extends IntegrationTestCase
{
    public function test_call_valid_endpoint(): void
    {
        $response = CamundaClient::make()->get('version');

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('version', $response->json());
        $this->assertInstanceOf(\stdClass::class, $response->object());
    }

    public function test_call_invalid_endpoint(): void
    {
        $response = CamundaClient::make()->get('invalid-endpoint');

        $this->assertEquals(404, $response->status());
    }
}
