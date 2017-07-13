<?php

namespace Wishlist\Tests\Domain;

use Mockery;
use Money\Currency;
use Money\Money;
use Wishlist\Domain\Moneybox;
use PHPUnit\Framework\TestCase;
use Wishlist\Domain\Wish;

class MoneyboxTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Deposit currency must match the fund one.
     */
    public function testDepositAndFundCurrenciesShouldBeTheSame()
    {
        $wish = Mockery::mock(Wish::class);
        $wish->shouldReceive('getCurrency')->andReturn(new Currency('USD'));
        $moneybox = new Moneybox($wish);

        $moneybox->deposit(new Money(150, new Currency('RUB')));
    }

    public function testDepositMustRecalculateFund()
    {
        $wish = Mockery::mock(Wish::class);
        $wish->shouldReceive('getCurrency')->andReturn(new Currency('USD'));
        $wish->shouldReceive('isPublished')->andReturn(true);
        $wish->shouldReceive('isFulfilled')->andReturn(false);
        $moneybox = new Moneybox($wish);

        $moneybox->deposit(new Money(150, new Currency('USD')));

        static::assertEquals(150, $moneybox->getFund()->getAmount());
    }
}
