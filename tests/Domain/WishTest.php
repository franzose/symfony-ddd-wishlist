<?php

namespace Wishlist\Tests\Domain;

use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Wishlist\Domain\Wish;

class WishTest extends TestCase
{
    /**
     * @param string|null $name
     * @expectedException \InvalidArgumentException
     * @dataProvider wishNamesDataProvider
     */
    public function testNameCannotBeEmpty($name)
    {
        new Wish(
            $name,
            new Money(1000, new Currency('USD')),
            new Money(100, new Currency('USD'))
        );
    }

    public function wishNamesDataProvider()
    {
        return [
            'Wish name cannot be NULL' => [null],
            'Wish name cannot be an empty string' => ['']
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPriceCannotBeZero()
    {
        new Wish(
            'Bicycle',
            new Money(0, new Currency('USD')),
            new Money(100, new Currency('USD'))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDailyFeeCannotBeZero()
    {
        new Wish(
            'Bicycle',
            new Money(1000, new Currency('USD')),
            new Money(0, new Currency('USD'))
        );
    }

    /**
     * @param string     $name
     * @param Money      $price
     * @param Money      $dailyFee
     * @param Money|null $fund
     *
     * @expectedException \InvalidArgumentException
     * @dataProvider currencyTestDataProvider
     */
    public function testThereShouldBeASingleCurrency(
        string $name,
        Money $price,
        Money $dailyFee,
        Money $fund = null
    ) {
        new Wish($name, $price, $dailyFee, $fund);
    }

    public function currencyTestDataProvider()
    {
        return [
            'Currencies of price and daily fee must match' => [
                'Bicycle',
                new Money(1000, new Currency('USD')),
                new Money(100, new Currency('RUB')),
                null
            ],
            'Currencies of price, daily fee and fun must match' => [
                'Bicycle',
                new Money(1000, new Currency('USD')),
                new Money(100, new Currency('USD')),
                new Money(100, new Currency('RUB'))
            ]
        ];
    }

    public function testInitialFundMustBeZero()
    {
        $wish = $this->createWishWithEmptyFund();

        static::assertTrue($wish->getFund()->isZero());
    }

    public function testInitialFundMustNotBeZero()
    {
        $wish = $this->createWishWithFund(500);
        $expected = new Money(500, new Currency('USD'));

        static::assertTrue($wish->getFund()->equals($expected));
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\WishIsAlreadyFulfilledException
     */
    public function testMustNotCreateAFulfilledWish()
    {
        $this->createWishWithPriceAndFund(100, 200);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDepositAndPriceCurrenciesMustMatch()
    {
        $wish = $this->createWishWithEmptyFund();

        $wish->deposit(new Money(125, new Currency('RUB')));
    }

    public function testSurplusFundsMustBe100()
    {
        $wish = $this->createWishWithPriceAndFund(500, 300);
        $wish->publish();

        $wish->deposit(new Money(100, new Currency('USD')));
        $wish->deposit(new Money(100, new Currency('USD')));

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
            'Bicycle',
            new Money(1000, new Currency('USD')),
            new Money(100, new Currency('USD'))
        );
    }

    private function createWishWithFund(int $fund): Wish
    {
        return new Wish(
            'Bicycle',
            new Money(1000, new Currency('USD')),
            new Money(100, new Currency('USD')),
            new Money($fund, new Currency('USD'))
        );
    }

    private function createWishWithPriceAndFund(int $price, int $fund): Wish
    {
        return new Wish(
            'Bicycle',
            new Money($price, new Currency('USD')),
            new Money(100, new Currency('USD')),
            new Money($fund, new Currency('USD'))
        );
    }
}
