<?php

namespace Wishlist\Application\Assembler;

use Wishlist\Application\Dto\ListDepositDto;
use Wishlist\Application\Dto\ListWishDto;
use Wishlist\Domain\Deposit;
use Wishlist\Domain\Wish;

class ListWishDtoAssembler
{
    /**
     * @param array|Wish[] $wishes
     *
     * @return array
     */
    public function toArrayOfDto(array $wishes): array
    {
        return array_map(function (Wish $wish) {
            $dto = new ListWishDto();
            $dto->id = $wish->getId()->getId();
            $dto->name = $wish->getName();
            $dto->fund = $wish->getFund()->getAmount();
            $dto->price = $wish->getPrice()->getAmount();
            $dto->createdAt = $wish->getCreatedAt()->format('d.m');
            $dto->isPublished = $wish->isPublished();
            $dto->currency = $wish->getCurrency()->getCode();
            $dto->deposits = $this->assembleDeposits($wish->getDeposits());

            return $dto;
        }, $wishes);
    }

    private function assembleDeposits(array $deposits)
    {
        return array_map(function (Deposit $deposit) {
            $dto = new ListDepositDto();
            $dto->amount = $deposit->getMoney()->getAmount();
            $dto->currency = $deposit->getMoney()->getCurrency()->getCode();
            $dto->createdAt = $deposit->getDate()->format('d.m.Y H:i');

            return $dto;
        }, $deposits);
    }
}
