<?php

namespace Wishlist\Tests\Domain;

use Mockery;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Wishlist\Domain\Deposit;
use Wishlist\Domain\DepositId;
use Wishlist\Domain\Wish;

class DepositTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDepositAmountMustNotBeZero()
    {
        $wish = Mockery::mock(Wish::class);
        $wish
            ->shouldReceive('isPublished')
            ->once()
            ->andReturn(true);

        $wish
            ->shouldReceive('isFulfilled')
            ->once()
            ->andReturn(false);

        $amount = new Money(0, new Currency('USD'));

        new Deposit(DepositId::next(), $wish, $amount);
    }
}
