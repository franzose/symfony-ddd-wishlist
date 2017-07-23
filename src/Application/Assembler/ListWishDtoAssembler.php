<?php

namespace Wishlist\Application\Assembler;

use Wishlist\Application\Dto\ListWishDto;
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

            return $dto;
        }, $wishes);
    }
}
