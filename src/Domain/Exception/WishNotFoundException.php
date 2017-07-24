<?php

namespace Wishlist\Domain\Exception;

use Exception;

class WishNotFoundException extends Exception
{
    public function __construct($wishId)
    {
        parent::__construct('Wish not found. ID: ' . (string) $wishId);
    }
}
