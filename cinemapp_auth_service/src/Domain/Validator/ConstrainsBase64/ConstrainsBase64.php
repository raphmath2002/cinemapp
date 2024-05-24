<?php

namespace Domain\Validator\ConstrainsBase64;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ConstrainsBase64 extends Constraint
{
    public string $message  = 'The string is not a valid base64 file';
    public string $mode     = 'strict';

    public function __construct(
        ?string $mode       = null,
        ?string $message    = null,
        ?array $groups      = null,
        $payload            = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
}