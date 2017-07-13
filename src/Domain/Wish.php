<?php

namespace Wishlist\Domain;

use Money\Currency;
use Money\Money;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsAlreadyFulfilledException;

class Wish
{
    private $id;
    private $name;
    private $price;
    private $moneybox;
    private $fee;
    private $published = false;
    private $fulfilled = false;

    public function __construct(
        WishId $id,
        WishName $name,
        Money $price,
        Money $fee,
        Money $fund = null
    ) {
        $this->makeIntegrityAssertions($price, $fee, $fund);

        $this->id       = $id;
        $this->name     = $name;
        $this->price    = $price;
        $this->fee      = $fee;
        $this->moneybox = new Moneybox($this, $fund);
    }

    private function makeIntegrityAssertions(Money $price, Money $fee, Money $fund = null): void
    {
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
        $this->moneybox->deposit($amount);
        $this->fulfillTheWishIfNeeded();
    }

    private function fulfillTheWishIfNeeded(): void
    {
        if ($this->moneybox->keepsEqualOrMore($this->price)) {
            $this->fulfilled = true;
        }
    }

    public function getFund(): Money
    {
        return $this->moneybox->getFund();
    }

    public function calculateSurplusFunds(): Money
    {
        $difference = $this->price->subtract($this->moneybox->getFund());

        return $difference->isNegative()
            ? $difference->absolute()
            : new Money(0, $this->getCurrency());
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

    public function getId(): WishId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getCurrency(): Currency
    {
        return $this->price->getCurrency();
    }
}
