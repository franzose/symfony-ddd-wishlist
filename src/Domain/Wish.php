<?php

namespace Wishlist\Domain;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Currency;
use Money\Money;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\DepositDoesNotExistException;
use Wishlist\Domain\Exception\WishIsInactiveException;

class Wish
{
    private $id;
    private $name;
    private $expense;
    private $deposits;
    private $fund;
    private $published = false;
    private $fulfilled = false;
    private $createdAt;
    private $updatedAt;

    public function __construct(WishId $id, WishName $name, Expense $expense)
    {
        $this->id = $id;
        $this->name = $name;
        $this->expense = $expense;
        $this->deposits = new ArrayCollection();
        $this->fund = $expense->getInitialFund();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deposit(Money $amount): DepositId
    {
        $this->assertCanDeposit($amount);

        $depositId = DepositId::next();
        $deposit = new Deposit($depositId, $this, $amount);
        $this->deposits->add($deposit);
        $this->fund = $this->fund->add($deposit->getMoney());

        $this->fulfillTheWishIfNeeded();

        return $depositId;
    }

    private function assertCanDeposit(Money $amount)
    {
        if (!$this->published || $this->fulfilled) {
            throw new WishIsInactiveException(
                'Deposit cannot be made.'
            );
        }

        Assert::true(
            $amount->isSameCurrency($this->expense->getPrice()),
            'Deposit currency must match the price\'s one.'
        );
    }

    private function fulfillTheWishIfNeeded(): void
    {
        if ($this->fund->greaterThanOrEqual($this->expense->getPrice())) {
            $this->fulfilled = true;
        }
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled;
    }

    public function withdraw(DepositId $depositId)
    {
        if (!$this->published || $this->fulfilled) {
            throw new WishIsInactiveException('Withdraw cannot be made.');
        }

        $deposit = $this->getDepositById($depositId);
        $this->deposits->removeElement($deposit);
        $this->fund = $this->fund->subtract($deposit->getMoney());
    }

    private function getDepositById(DepositId $depositId): Deposit
    {
        $deposit = $this->deposits->filter(
            function (Deposit $deposit) use ($depositId) {
                return $deposit->getId()->equalTo($depositId);
            }
        )->first();

        if (!$deposit) {
            throw new DepositDoesNotExistException($depositId);
        }

        return $deposit;
    }

    public function calculateSurplusFunds(): Money
    {
        $difference = $this->getPrice()->subtract($this->fund);

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
        return $this->fund;
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
