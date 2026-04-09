<?php

namespace LanSoftware\LanCoreClient\Exceptions;

class LanCoreDisabledException extends LanCoreException
{
    public function __construct(string $message = 'LanCore integration is disabled.')
    {
        parent::__construct($message);
    }
}
