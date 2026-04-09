<?php

namespace LanSoftware\LanCoreClient\Exceptions;

class LanCoreUnavailableException extends LanCoreException
{
    public function __construct(string $message = 'LanCore is unreachable.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
