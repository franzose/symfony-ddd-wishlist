<?php

namespace Wishlist\Domain;

use DateTime;
use Money\Money;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsUnavailableToDepositException;

class Deposit
{
    private $id;
    private $wish;
    private $amount;
    private $date;

    public function __construct(DepositId $id, Wish $wish, Money $amount)
    {
        $this->makeIntegrityAssertions($wish, $amount);

        $this->id = $id;
        $this->wish = $wish;
        $this->amount = $amount;
        $this->date = new DateTime('now');
    }

    private function makeIntegrityAssertions(Wish $wish, Money $amount): void
    {
        if (!$wish->isPublished() || $wish->isFulfilled()) {
            throw new WishIsUnavailableToDepositException();
        }

        Assert::false($amount->isZero(), 'Deposit must not be empty.');
    }

    public function getId(): DepositId
    {
        return $this->id;
    }

    public function getWish(): Wish
    {
        return $this->wish;
    }

    public function getMoney(): Money
    {
        return $this->amount;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
