<?php

namespace Laravolt\Camunda;

class BpmnReader
{
    private \SimpleXMLElement $xml;

    public function __construct(
        private string $file
    ) {
        $this->xml = new \SimpleXMLElement(file_get_contents($file));
        $this->xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
        $this->xml->registerXPathNamespace('camunda', 'http://camunda.org/schema/1.0/bpmn');
    }

    public function getForms()
    {
        $nodes = array_merge($this->xml->xpath('//bpmn:startEvent'), $this->xml->xpath('//bpmn:userTask'));

        $forms = [];
        foreach ($nodes as $node) {
            try {
                $fields = $node->xpath('bpmn:extensionElements/camunda:formData/camunda:formField');
                $formFields = [];
                foreach ($fields as $field) {
                    $formFields[] = [
                        'id' => (string)$field->attributes()->id,
                        'label' => (string)$field->attributes()->label,
                        'type' => (string)$field->attributes()->type,
                    ];
                }
                $form = [
                    'id' => (string)$node->attributes()->id,
                    'label' => (string)$node->attributes()->name,
                    'fields' => $formFields
                ];
                $forms[] = [];
            } catch (\ErrorException $exception) {

            }
        }

        return $forms;
    }

    public function getXml(): \SimpleXMLElement
    {
        return $this->xml;
    }
}
