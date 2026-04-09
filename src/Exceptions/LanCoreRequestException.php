<?php

namespace LanSoftware\LanCoreClient\Exceptions;

class LanCoreRequestException extends LanCoreException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
