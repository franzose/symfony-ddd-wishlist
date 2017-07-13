<?php

namespace Wishlist\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Money\Money;
use Wishlist\Util\ExtendedCollection;

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
        $this->recalculateFund();
    }

    private function recalculateFund()
    {
        $this->fund = (new ExtendedCollection($this->deposits))
            ->reduce(function (Money $fund, Deposit $deposit) {
                return $deposit->getMoney()->add($fund);
            }, $this->fund);
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
