<?php

namespace Wishlist\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Wishlist\Domain\Exception\DepositDoesNotExistException;

final class Moneybox
{
    private $wish;
    private $fund;
    private $deposits;

    public function __construct(Wish $wish, Money $fund = null)
    {
        $this->wish = $wish;
        $this->fund = $fund ?? new Money(0, $wish->getCurrency());
        $this->deposits = new ArrayCollection();
    }

    public function deposit(Deposit $deposit)
    {
        $this->deposits->add($deposit);
        $this->fund = $this->fund->add($deposit->getMoney());
    }

    public function withdraw(DepositId $depositId)
    {
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

    public function getFund()
    {
        return $this->fund;
    }

    public function keepsEqualOrMore(Money $amount)
    {
        return $this->fund->greaterThanOrEqual($amount);
    }
}
