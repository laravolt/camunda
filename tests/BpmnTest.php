<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\BpmnReader;

class BpmnTest extends TestCase
{
    public function test_parse_form_definition(): void
    {
        $file = __DIR__.'/../resources/rekrutmen-sederhana.bpmn';
        $reader = new BpmnReader($file);
        $forms = $reader->getForms();

        $this->assertNotEmpty($forms);
    }

    public function test_parse_empty_form_definition(): void
    {
        $file = __DIR__.'/../resources/sample.bpmn';
        $reader = new BpmnReader($file);
        $forms = $reader->getForms();

        $this->assertEmpty($forms);
    }
}
