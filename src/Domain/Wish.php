<?php

namespace Wishlist\Domain;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Money\Currency;
use Money\Money;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsUnavailableToDepositException;

class Wish
{
    private $id;
    private $name;
    private $moneybox;
    private $expense;
    private $published = false;
    private $fulfilled = false;
    private $createdAt;
    private $updatedAt;

    public function __construct(WishId $id, WishName $name, Expense $expense)
    {
        $this->id = $id;
        $this->name = $name;
        $this->expense = $expense;
        $this->moneybox = new Moneybox($this, $this->expense->getInitialFund());
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
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

    public function predictFulfillmentDateBasedOnFee(): DateTimeInterface
    {
        $daysToGo = ceil(
            $this->getPrice()
            ->divide($this->getFee()->getAmount())
            ->getAmount()
        );

        return $this->createFutureDate($daysToGo);
    }

    public function predictFulfillmentDateBasedOnFund(): DateTimeInterface
    {
        $daysToGo = ceil(
            $this->getPrice()
            ->subtract($this->getFund())
            ->divide($this->getFee()->getAmount())
            ->getAmount()
        );

        return $this->createFutureDate($daysToGo);
    }

    private function createFutureDate($daysToGo): DateTimeInterface
    {
        return (new DateTimeImmutable())->add(new DateInterval("P{$daysToGo}D"));
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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
