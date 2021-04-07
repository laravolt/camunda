<?php

namespace Laravolt\Camunda\Tests\Models;

use Laravolt\Camunda\BpmnReader;
use Laravolt\Camunda\Tests\TestCase;

class BpmnTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_parse_form_definition(): void
    {
        $file = __DIR__.'/../../resources/rekrutmen-sederhana.bpmn';
        $reader = new BpmnReader($file);
        $forms = $reader->getForms();

        $this->assertNotEmpty($forms);
    }

    /**
     * @test
     */
    public function it_can_handle_bpmn_without_form_definition(): void
    {
        $file = __DIR__.'/../../resources/sample.bpmn';
        $reader = new BpmnReader($file);
        $forms = $reader->getForms();

        $this->assertEmpty($forms);
    }
}
