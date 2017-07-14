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
        $wish->shouldReceive('getCurrency')->once()->andReturn(new Currency('USD'));
        $wish->shouldReceive('isPublished')->once()->andReturn(true);
        $wish->shouldReceive('isFulfilled')->once()->andReturn(false);
        $moneybox = new Moneybox($wish);
        $depositOne = new Deposit(DepositId::next(), $wish, new Money(150, new Currency('USD')));
        $depositTwo = new Deposit(DepositId::next(), $wish, new Money(150, new Currency('USD')));

        $moneybox->deposit($depositOne);
        $moneybox->deposit($depositTwo);

        static::assertEquals(300, $moneybox->getFund()->getAmount());
    }

    public function testWithdrawMustRecalculateFund()
    {
        $wish = Mockery::mock(Wish::class);
        $wish->shouldReceive('getCurrency')->once()->andReturn(new Currency('USD'));
        $wish->shouldReceive('isPublished')->once()->andReturn(true);
        $wish->shouldReceive('isFulfilled')->once()->andReturn(false);
        $moneybox = new Moneybox($wish);
        $depositIdOne = DepositId::next();
        $depositIdTwo = DepositId::next();
        $depositOne = new Deposit($depositIdOne, $wish, new Money(150, new Currency('USD')));
        $depositTwo = new Deposit($depositIdTwo, $wish, new Money(150, new Currency('USD')));

        $moneybox->deposit($depositOne);
        $moneybox->deposit($depositTwo);
        $moneybox->withdraw($depositIdOne);

        static::assertEquals(150, $moneybox->getFund()->getAmount());
    }
}
