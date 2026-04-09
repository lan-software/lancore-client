<?php

namespace LanSoftware\LanCoreClient\Exceptions;

class InvalidLanCoreUserException extends LanCoreException
{
    public function __construct(string $message = 'Invalid LanCore user payload.')
    {
        parent::__construct($message);
    }
}
