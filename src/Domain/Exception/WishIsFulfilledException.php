<?php

namespace Wishlist\Domain\Exception;

use Exception;
use Wishlist\Domain\WishId;

class WishIsFulfilledException extends Exception implements DomainExceptionInterface, InvalidOperationExceptionInterface
{
    public function __construct(WishId $wishId)
    {
        parent::__construct('The wish is fulfilled. ID: ' . $wishId);
    }
}
