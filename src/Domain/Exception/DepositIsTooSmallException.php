<?php

namespace Wishlist\Domain\Exception;

use Exception;
use Money\Money;

class DepositIsTooSmallException extends Exception
{
    public function __construct(Money $deposit, Money $fee)
    {
        parent::__construct(
            sprintf(
                'Deposit %s %s is too small. It must not be less than %s %s',
                $deposit->getAmount(),
                $deposit->getCurrency(),
                $fee->getAmount(),
                $fee->getCurrency()
            )
        );
    }
}
