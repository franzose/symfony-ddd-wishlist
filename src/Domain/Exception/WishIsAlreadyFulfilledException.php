<?php

namespace Wishlist\Domain\Exception;

use Exception;

class WishIsAlreadyFulfilledException extends Exception
{
    public function __construct()
    {
        parent::__construct('');
    }
}
