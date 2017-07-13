<?php

namespace Wishlist\Tests\Domain;

use DateInterval;
use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Wishlist\Domain\Expense;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishName;

class WishTest extends TestCase
{
    /**
     * @param Wish $wish
     * @expectedException \Wishlist\Domain\Exception\WishIsUnavailableToDepositException
     * @dataProvider mustNotDepositDataProvider
     */
    public function testMustNotDeposit(Wish $wish)
    {
        $wish->deposit(new Money(100, new Currency('USD')));
        $wish->deposit(new Money(100, new Currency('USD')));
    }

    public function mustNotDepositDataProvider()
    {
        $fulfilled = $this->createWishWithPriceAndFund(500, 450);
        $fulfilled->publish();

        return [
            'Should not create a deposit if the wish is unpublished' => [
                $this->createWishWithEmptyFund()
            ],
            'Should not create a deposit if the wish is already fulfilled' => [
                $fulfilled
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDepositAndPriceCurrenciesMustMatch()
    {
        $wish = $this->createWishWithEmptyFund();
        $wish->publish();

        $wish->deposit(new Money(125, new Currency('RUB')));
    }

    public function testSurplusFundsMustBe100()
    {
        $wish = $this->createWishWithPriceAndFund(500, 300);
        $wish->publish();

        $wish->deposit(new Money(100, new Currency('USD')));
        $wish->deposit(new Money(200, new Currency('USD')));

        $expected = new Money(100, new Currency('USD'));
        static::assertTrue($wish->calculateSurplusFunds()->equals($expected));
    }

    public function testSurplusFundsMustBeZero()
    {
        $wish = $this->createWishWithPriceAndFund(500, 250);
        $wish->publish();

        $wish->deposit(new Money(100, new Currency('USD')));

        $expected = new Money(0, new Currency('USD'));
        static::assertTrue($wish->calculateSurplusFunds()->equals($expected));
    }

    public function testFulfillmentDatePredictionBasedOnFee()
    {
        $price = 1500;
        $fee = 20;
        $wish = $this->createWishWithPriceAndFee($price, $fee);
        $daysToGo = ceil($price / $fee);

        $expected = (new DateTimeImmutable())->add(new DateInterval("P{$daysToGo}D"));
        $diff = $wish->predictFulfillmentDateBasedOnFee()->diff($expected);
        static::assertTrue($diff->d === 0);
        static::assertTrue($diff->m === 0);
        static::assertTrue($diff->y === 0);
        static::assertTrue($diff->h === 0);
        static::assertTrue($diff->i === 0);
        static::assertTrue($diff->s === 0);
    }

    public function testFulfillmentDatePredictionBasedOnFund()
    {
        $price = 1500;
        $fund = 250;
        $fee = 25;
        $wish = $this->createWish($price, $fee, $fund);
        $daysToGo = ceil(($price - $fund) / $fee);

        $expected = (new DateTimeImmutable())->add(new DateInterval("P{$daysToGo}D"));
        $diff = $wish->predictFulfillmentDateBasedOnFund()->diff($expected);
        static::assertTrue($diff->d === 0);
        static::assertTrue($diff->m === 0);
        static::assertTrue($diff->y === 0);
        static::assertTrue($diff->h === 0);
        static::assertTrue($diff->i === 0);
        static::assertTrue($diff->s === 0);
    }

    public function testPublishShouldPublishTheWish()
    {
        $wish = $this->createWishWithEmptyFund();

        $wish->publish();

        static::assertTrue($wish->isPublished());
    }

    public function testUnpublishShouldUnpublishTheWish()
    {
        $wish = $this->createWishWithEmptyFund();

        $wish->unpublish();

        static::assertFalse($wish->isPublished());
    }

    private function createWishWithEmptyFund(): Wish
    {
        return new Wish(
            WishId::next(),
            new WishName('Bicycle'),
            Expense::fromCurrencyAndScalars(
                new Currency('USD'),
                1000,
                100
            )
        );
    }

    private function createWishWithPriceAndFund(int $price, int $fund): Wish
    {
        return new Wish(
            WishId::next(),
            new WishName('Bicycle'),
            Expense::fromCurrencyAndScalars(
                new Currency('USD'),
                $price,
                10,
                $fund
            )
        );
    }

    private function createWishWithPriceAndFee(int $price, int $fee): Wish
    {
        return new Wish(
            WishId::next(),
            new WishName('Bicycle'),
            Expense::fromCurrencyAndScalars(
                new Currency('USD'),
                $price,
                $fee
            )
        );
    }

    private function createWish(int $price, int $fee, int $fund): Wish
    {
        return new Wish(
            WishId::next(),
            new WishName('Bicycle'),
            Expense::fromCurrencyAndScalars(
                new Currency('USD'),
                $price,
                $fee,
                $fund
            )
        );
    }
}
