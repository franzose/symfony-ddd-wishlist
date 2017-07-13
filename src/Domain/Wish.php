<?php

namespace Wishlist\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsAlreadyFulfilledException;
use Wishlist\Util\ExtendedCollection;

class Wish
{
    private $id;
    private $name;
    private $price;
    private $moneybox;
    private $fund;
    private $fee;
    private $published = false;
    private $fulfilled = false;

    public function __construct(
        $name,
        Money $price,
        Money $fee,
        Money $fund = null
    ) {
        $this->makeIntegrityAssertions($name, $price, $fee, $fund);

        $this->id       = Uuid::uuid4();
        $this->name     = $name;
        $this->price    = $price;
        $this->fee      = $fee;
        $this->moneybox = new ArrayCollection();
        $this->fund = $fund ?? $this->createZeroAmountOfMoney();
    }

    private function makeIntegrityAssertions($name, Money $price, Money $fee, Money $fund = null): void
    {
        Assert::notEmpty($name, 'Name must not be null.');
        Assert::true($price->isSameCurrency($fee), 'Currencies must match.');
        Assert::true($price->isPositive(), 'Price cannot be zero');
        Assert::true($fee->isPositive(), 'Fee must not be zero.');

        $this->disallowCreatingAFulfilledWish($price, $fund);

        if ($fund instanceof Money) {
            Assert::true($fund->isSameCurrency($price), 'Currencies must match.');
        }
    }

    private function disallowCreatingAFulfilledWish(Money $price, Money $fund = null): void
    {
        if (null !== $fund && $fund->greaterThanOrEqual($price)) {
            throw new WishIsAlreadyFulfilledException();
        }
    }

    public function deposit(Money $amount)
    {
        Assert::true($amount->isSameCurrency($this->price), 'Currencies must match.');

        $this->moneybox->add(new Deposit($this, $amount));
        $this->recalculateFund();
        $this->fulfillTheWishIfNeeded();
    }

    private function recalculateFund()
    {
        $this->fund = (new ExtendedCollection($this->moneybox))
            ->reduce(function (Money $fund, Deposit $deposit) {
                return $deposit->getMoney()->add($fund);
            }, $this->fund);
    }

    private function fulfillTheWishIfNeeded(): void
    {
        if ($this->fund->greaterThanOrEqual($this->price)) {
            $this->fulfilled = true;
        }
    }

    public function getFund(): Money
    {
        return $this->fund;
    }

    public function calculateSurplusFunds(): Money
    {
        $difference = $this->price->subtract($this->fund);

        return $difference->isNegative()
            ? $difference->absolute()
            : $this->createZeroAmountOfMoney();
    }

    private function createZeroAmountOfMoney(): Money
    {
        return new Money(0, $this->price->getCurrency());
    }

    public function getFee(): Money
    {
        return $this->fee;
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function publish()
    {
        $this->published = true;
    }

    public function unpublish()
    {
        $this->published = false;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }
}
