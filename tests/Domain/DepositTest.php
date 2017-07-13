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
     * @param Wish $wish
     * @expectedException \Wishlist\Domain\Exception\WishIsUnavailableToDepositException
     * @dataProvider nonAcceptableWishDataProvider
     */
    public function testMustNotCreateContribution($wish)
    {
        $amount = new Money(100, new Currency('USD'));

        new Deposit(DepositId::next(), $wish, $amount);
    }

    public function nonAcceptableWishDataProvider()
    {
        $unpublished = Mockery::mock(Wish::class);
        $unpublished
            ->shouldReceive('isPublished')
            ->once()
            ->andReturn(false);

        $fulfilled = Mockery::mock(Wish::class);
        $fulfilled
            ->shouldReceive('isPublished')
            ->once()
            ->andReturn(true);

        $fulfilled
            ->shouldReceive('isFulfilled')
            ->once()
            ->andReturn(true);

        return [
            'Should not create a deposit if the wish is unpublished' => [$unpublished],
            'Should not create a deposit if the wish is already fulfilled' => [$fulfilled]
        ];
    }

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
