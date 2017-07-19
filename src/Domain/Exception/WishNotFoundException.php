<?php

namespace Wishlist\Domain\Exception;

use Exception;
use Wishlist\Domain\WishId;

class WishNotFoundException extends Exception
{
    public function __construct(WishId $wishId)
    {
        parent::__construct('Wish not found. ID: ' . $wishId);
    }
}
