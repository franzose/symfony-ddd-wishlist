<?php

namespace Wishlist\Infrastructure\Currency;

use Money\Currency;

class DefaultCurrencyFactory
{
    public static function createCurrency(string $code = 'USD'): Currency
    {
        return new Currency($code);
    }
}
