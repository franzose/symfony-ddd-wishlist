<?php

namespace Wishlist\Application;

use Money\Currency;
use Money\Money;
use Wishlist\Application\Assembler\ListWishDtoAssembler;
use Wishlist\Application\Dto\NewWishDto;
use Wishlist\Domain\DepositId;
use Wishlist\Domain\Exception\InvalidIdentityException;
use Wishlist\Domain\Exception\WishNotFoundException;
use Wishlist\Domain\Expense;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishName;
use Wishlist\Domain\WishRepositoryInterface;

class Wishlist implements WishlistInterface
{
    private $wishes;
    private $currency;

    public function __construct(WishRepositoryInterface $wishes, Currency $currency)
    {
        $this->wishes = $wishes;
        $this->currency = $currency;
    }

    public function getWishesByPage(int $page, int $limit): array
    {
        $wishes = $this->wishes->slice($page * $limit, $limit);

        return (new ListWishDtoAssembler())->toArrayOfDto($wishes);
    }

    public function addNewWish(NewWishDto $dto): string
    {
        $wishId = WishId::next();
        $this->wishes->put($this->createWishFromIdAndDto($wishId, $dto));

        return $wishId->getId();
    }

    private function createWishFromIdAndDto(WishId $wishId, NewWishDto $dto): Wish
    {
        $wish = new Wish(
            $wishId,
            new WishName($dto->name),
            Expense::fromCurrencyAndScalars(
                $this->currency,
                $dto->price,
                $dto->fee,
                $dto->initialFund
            )
        );

        if ($dto->isPublished) {
            $wish->publish();
        }

        return $wish;
    }

    public function deposit(string $wishId, int $amount): string
    {
        $wish = $this->getWish($wishId);
        $depositId = $wish->deposit(new Money($amount, $this->currency));
        $this->wishes->put($wish);

        return $depositId->getId();
    }

    public function withdraw(string $wishId, string $depositId): Money
    {
        $wish = $this->getWish($wishId);
        $wish->withdraw(DepositId::fromString($depositId));
        $this->wishes->put($wish);

        return $wish->getFund();
    }

    public function publish(string $wishId)
    {
        $wish = $this->getWish($wishId);
        $wish->publish();
        $this->wishes->put($wish);
    }

    public function unpublish(string $wishId)
    {
        $wish = $this->getWish($wishId);
        $wish->unpublish();
        $this->wishes->put($wish);
    }

    private function getWish(string $wishId): Wish
    {
        try {
            return $this->wishes->get(WishId::fromString($wishId));
        } catch (InvalidIdentityException $ex) {
            throw new WishNotFoundException($wishId);
        }
    }

    public function getTotalWishesNumber(): int
    {
        return $this->wishes->count();
    }
}
