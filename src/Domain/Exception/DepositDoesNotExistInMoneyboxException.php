<?php

namespace Wishlist\Domain\Exception;

use Exception;

class DepositDoesNotExistInMoneyboxException extends Exception
{
    public function __construct()
    {
        parent::__construct('Deposit does not exist.');
    }
}
