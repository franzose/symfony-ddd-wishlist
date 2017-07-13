<?php

namespace Wishlist\Domain;

use Money\Currency;
use Money\Money;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsUnavailableToDepositException;

class Wish
{
    private $id;
    private $name;
    private $moneybox;
    private $published = false;
    private $fulfilled = false;
    private $expense;

    public function __construct(WishId $id, WishName $name, Expense $expense)
    {
        $this->id = $id;
        $this->name = $name;
        $this->expense = $expense;
        $this->moneybox = new Moneybox($this, $this->expense->getInitialFund());
    }

    public function deposit(Money $amount)
    {
        $this->assertCanDeposit($amount);

        $this->moneybox->deposit(new Deposit(DepositId::next(), $this, $amount));

        $this->fulfillTheWishIfNeeded();
    }

    private function assertCanDeposit(Money $amount)
    {
        if (!$this->published || $this->fulfilled) {
            throw new WishIsUnavailableToDepositException();
        }

        Assert::true(
            $amount->isSameCurrency($this->expense->getPrice()),
            'Deposit currency must match the price\'s one.'
        );
    }

    private function fulfillTheWishIfNeeded(): void
    {
        if ($this->moneybox->keepsEqualOrMore($this->expense->getPrice())) {
            $this->fulfilled = true;
        }
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled;
    }

    public function withdraw(Deposit $deposit)
    {
        $this->moneybox->withdraw($deposit);
    }

    public function calculateSurplusFunds(): Money
    {
        $difference = $this->expense->getPrice()->subtract($this->moneybox->getFund());

        return $difference->isNegative()
            ? $difference->absolute()
            : new Money(0, $this->getCurrency());
    }

    public function publish()
    {
        $this->published = true;
    }

    public function unpublish()
    {
        $this->published = false;
    }

    public function isPublished(): bool
    {
        return $this->published;
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
        return $this->expense->getPrice();
    }

    public function getFee(): Money
    {
        return $this->expense->getFee();
    }

    public function getFund(): Money
    {
        return $this->moneybox->getFund();
    }

    public function getCurrency(): Currency
    {
        return $this->expense->getCurrency();
    }
}
