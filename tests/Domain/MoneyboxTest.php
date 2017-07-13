<?php

namespace Wishlist\Tests\Domain;

use Mockery;
use Money\Currency;
use Money\Money;
use Wishlist\Domain\Deposit;
use Wishlist\Domain\DepositId;
use Wishlist\Domain\Moneybox;
use PHPUnit\Framework\TestCase;
use Wishlist\Domain\Wish;

class MoneyboxTest extends TestCase
{
    public function testDepositMustRecalculateFund()
    {
        $wish = Mockery::mock(Wish::class);
        $wish->shouldReceive('getCurrency')->andReturn(new Currency('USD'));
        $wish->shouldReceive('isPublished')->andReturn(true);
        $wish->shouldReceive('isFulfilled')->andReturn(false);
        $moneybox = new Moneybox($wish);
        $deposit = new Deposit(DepositId::next(), $wish, new Money(150, new Currency('USD')));

        $moneybox->deposit($deposit);

        static::assertEquals(150, $moneybox->getFund()->getAmount());
    }
}
