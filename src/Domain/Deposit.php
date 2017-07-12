<?php

namespace Wishlist\Domain;

use DateTime;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishIsUnavailableToDepositException;

class Deposit
{
    private $id;
    private $wish;
    private $amount;
    private $date;

    public function __construct(Wish $wish, Money $amount)
    {
        if (!$wish->isPublished() || $wish->isFulfilled()) {
            throw new WishIsUnavailableToDepositException();
        }

        Assert::false($amount->isZero(), 'Deposit must not be empty.');

        $this->id = Uuid::uuid4();
        $this->wish = $wish;
        $this->amount = $amount;
        $this->date = new DateTime('now');
    }

    public function getId(): UuidInterface
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
