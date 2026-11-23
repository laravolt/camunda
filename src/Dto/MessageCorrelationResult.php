<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Spatie\LaravelData\Data;

/**
 * One result of a message correlation: a waiting execution that received the
 * message (`Execution`), or a process instance the message started (`ProcessDefinition`).
 */
class MessageCorrelationResult extends Data
{
    public function __construct(
        public string $resultType,
        public ?ProcessInstance $processInstance = null,
        public ?array $execution = null,
        /** Process variables after the correlation, when they were requested. */
        public ?array $variables = null,
    ) {}
}
