<?php

namespace Wishlist\Domain\Exception;

use Exception;

class WishIsUnavailableToDepositException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'It is impossible to deposit to wishes that are not published or has already fulfilled.'
        );
    }
}
