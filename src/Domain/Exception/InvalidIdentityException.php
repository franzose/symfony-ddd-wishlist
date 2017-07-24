<?php

namespace Wishlist\Domain\Exception;

use Exception;

class InvalidIdentityException extends Exception
{
    public function __construct($identifier)
    {
        parent::__construct('Invalid identity: ' . (string) $identifier);
    }
}
