<?php

namespace Wishlist\Domain\Exception;

use Exception;
use Wishlist\Domain\DepositId;

class DepositDoesNotExistException extends Exception implements DomainExceptionInterface, NotFoundExceptionInterface
{
    public function __construct(DepositId $id)
    {
        parent::__construct('Deposit does not exist: ' . $id);
    }
}
