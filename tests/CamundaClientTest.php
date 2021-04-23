<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Http\CamundaClient;

class CamundaClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_call_valid_endpoint(): void
    {
        $response = CamundaClient::make()->get('version');

        $this->assertEquals(200, $response->status());
        $this->assertInstanceOf(\stdClass::class, $response->object());
    }

    /**
     * @test
     */
    public function it_cannot_call_invalid_endpoint(): void
    {
        $response = CamundaClient::make()->get('invalid-endpoint');

        $this->assertEquals(404, $response->status());
    }
}
