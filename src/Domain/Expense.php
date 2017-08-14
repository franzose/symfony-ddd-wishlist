<?php

namespace Wishlist\Domain;

use Money\Currency;
use Money\Money;
use Webmozart\Assert\Assert;

final class Expense
{
    private $price;
    private $fee;
    private $initialFund;

    private function __construct(Money $price, Money $fee, Money $initialFund)
    {
        $this->price = $price;
        $this->fee = $fee;
        $this->initialFund = $initialFund;
    }

    public static function fromCurrencyAndScalars(
        Currency $currency,
        int $price,
        int $fee,
        int $initialFund = null
    ) {
        foreach ([$price, $fee] as $argument) {
            Assert::notEmpty($argument);
            Assert::greaterThan($argument, 0);
        }

        Assert::lessThan($fee, $price, 'Fee must be less than price.');

        if (null !== $initialFund) {
            Assert::greaterThanEq($initialFund, 0);
            Assert::lessThan($initialFund, $price, 'Initial fund must be less than price.');
        }

        return new static(
            new Money($price, $currency),
            new Money($fee, $currency),
            new Money($initialFund ?? 0, $currency)
        );
    }

    public function getCurrency(): Currency
    {
        return $this->price->getCurrency();
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function changePrice(Money $amount): Expense
    {
        Assert::true($amount->getCurrency()->equals($this->getCurrency()));

        return new static($amount, $this->fee, $this->initialFund);
    }

    public function getFee(): Money
    {
        return $this->fee;
    }

    public function changeFee(Money $amount): Expense
    {
        Assert::true($amount->getCurrency()->equals($this->getCurrency()));

        return new static($this->price, $amount, $this->initialFund);
    }

    public function getInitialFund(): Money
    {
        return $this->initialFund;
    }
}
