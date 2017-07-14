<?php

namespace Wishlist\Domain\Exception;

use Exception;
use Wishlist\Domain\DepositId;

class DepositDoesNotExistException extends Exception
{
    public function __construct(DepositId $id)
    {
        parent::__construct('Deposit does not exist: ' . $id);
    }
}
