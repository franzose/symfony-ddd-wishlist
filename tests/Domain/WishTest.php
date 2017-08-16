<?php

namespace Wishlist\Tests\Domain;

use DateInterval;
use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Wishlist\Domain\DepositId;
use Wishlist\Domain\Expense;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishName;

class WishTest extends TestCase
{
    public function testCreatedAndUpdatedAtMustBeEqualUponCreation()
    {
        $wish = $this->createWishWithEmptyFund();
        $diff = $wish->getCreatedAt()->diff($wish->getUpdatedAt());

        static::assertNotSame($wish->getCreatedAt(), $wish->getUpdatedAt());
        static::assertTrue($diff->y === 0);
        static::assertTrue($diff->m === 0);
        static::assertTrue($diff->d === 0);
        static::assertTrue($diff->h === 0);
        static::assertTrue($diff->i === 0);
        static::assertTrue($diff->s === 0);
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\DepositIsTooSmallException
     */
    public function testMustDeclineDepositIfItIsLessThanFee()
    {
        $wish = $this->createWishWithPriceAndFee(1000, 100);
        $wish->publish();

        $wish->deposit(new Money(50, new Currency('USD')));
    }

    public function testExtraDepositMustFulfillTheWish()
    {
        $wish = $this->createWishWithPriceAndFund(1000, 900);
        $wish->publish();

        $wish->deposit(new Money(150, new Currency('USD')));

        static::assertTrue($wish->isFulfilled());
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\WishIsUnpublishedException
     */
    public function testMustNotDepositWhenUnpublished()
    {
        $wish = $this->createWishWithEmptyFund();
        $wish->deposit(new Money(100, new Currency('USD')));
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\WishIsFulfilledException
     */
    public function testMustNotDepositWhenFulfilled()
    {
        $fulfilled = $this->createWishWithPriceAndFund(500, 450);
        $fulfilled->publish();

        $fulfilled->deposit(new Money(100, new Currency('USD')));
        $fulfilled->deposit(new Money(100, new Currency('USD')));
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\WishIsUnpublishedException
     */
    public function testMustNotWithdrawIfUnpublished()
    {
        $wish = $this->createWishWithPriceAndFund(500, 0);
        $wish->publish();
        $deposit = $wish->deposit(new Money(100, new Currency('USD')));
        $wish->unpublish();

        $wish->withdraw($deposit->getId());
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\WishIsFulfilledException
     */
    public function testMustNotWithdrawIfFulfilled()
    {
        $wish = $this->createWishWithPriceAndFund(500, 450);
        $wish->publish();
        $deposit = $wish->deposit(new Money(100, new Currency('USD')));

        $wish->withdraw($deposit->getId());
    }

    /**
     * @expectedException \Wishlist\Domain\Exception\DepositDoesNotExistException
     */
    public function testWithdrawMustThrowOnNonExistentId()
    {
        $wish = $this->createWishWithEmptyFund();
        $wish->publish();

        $wish->withdraw(DepositId::next());
    }

    public function testDepositShouldAddDepositToInternalCollection()
    {
        $wish = $this->createWishWithEmptyFund();
        $wish->publish();
        $depositMoney = new Money(150, new Currency('USD'));

        $wish->deposit($depositMoney);

        $deposits = $wish->getDeposits();
        static::assertCount(1, $deposits);
        static::assertArrayHasKey(0, $deposits);

        $deposit = $deposits[0];
        static::assertTrue($deposit->getMoney()->equals($depositMoney));
        static::assertSame($wish, $deposit->getWish());
    }

    public function testWithdrawShouldRemoveDepositFromInternalCollection()
    {
        $wish = $this->createWishWithEmptyFund();
        $wish->publish();
        $wish->deposit(new Money(150, new Currency('USD')));

        $wish->withdraw($wish->getDeposits()[0]->getId());

        static::assertCount(0, $wish->getDeposits());
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
        $updatedAt = $wish->getUpdatedAt();

        $wish->publish();

        static::assertTrue($wish->isPublished());
        static::assertNotSame($updatedAt, $wish->getUpdatedAt());
    }

    public function testUnpublishShouldUnpublishTheWish()
    {
        $wish = $this->createWishWithEmptyFund();
        $updatedAt = $wish->getUpdatedAt();

        $wish->unpublish();

        static::assertFalse($wish->isPublished());
        static::assertNotSame($updatedAt, $wish->getUpdatedAt());
    }

    public function testChangePrice()
    {
        $wish = $this->createWishWithPriceAndFee(1000, 10);
        $expected = new Money(1500, new Currency('USD'));
        $updatedAt = $wish->getUpdatedAt();

        static::assertSame($updatedAt, $wish->getUpdatedAt());

        $wish->changePrice($expected);

        static::assertTrue($wish->getPrice()->equals($expected));
        static::assertNotSame($updatedAt, $wish->getUpdatedAt());
    }

    public function testChangeFee()
    {
        $wish = $this->createWishWithPriceAndFee(1000, 10);
        $expected = new Money(50, new Currency('USD'));
        $updatedAt = $wish->getUpdatedAt();

        static::assertSame($updatedAt, $wish->getUpdatedAt());

        $wish->changeFee($expected);

        static::assertTrue($wish->getFee()->equals($expected));
        static::assertNotSame($updatedAt, $wish->getUpdatedAt());
    }

    public function testGetName()
    {
        $wish = $this->createWishWithName('foo');

        static::assertEquals('foo', $wish->getName());
    }

    private function createWishWithName(string $name): Wish
    {
        return new Wish(
            WishId::next(),
            new WishName($name),
            Expense::fromCurrencyAndScalars(
                new Currency('USD'),
                1000,
                100
            )
        );
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
