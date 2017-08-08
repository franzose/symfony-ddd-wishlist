<?php

namespace Wishlist\Tests\Application;

use Money\Currency;
use Money\Money;
use Wishlist\Application\Dto\ListWishDto;
use Wishlist\Application\Dto\NewWishDto;
use Wishlist\Application\Wishlist;
use PHPUnit\Framework\TestCase;
use Wishlist\Domain\Expense;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishName;
use Wishlist\Infrastructure\Persistence\Memory\WishRepository;

class WishlistTest extends TestCase
{
    /**
     * @var array|WishId[]
     */
    private $wishIds = [];
    private $wishIdsNumber = 0;

    public function setUp()
    {
        parent::setUp();

        foreach (range(0, 15) as $index) {
            $this->wishIds[] = WishId::next();
        }

        $this->wishIdsNumber = count($this->wishIds);
    }

    public function testGetWishesByPage()
    {
        $page = 2;
        $limit = 5;
        $repository = new WishRepository($this->createWishes());
        $wishlist = new Wishlist($repository, new Currency('USD'));

        $wishes = $wishlist->getWishesByPage($page, $limit);

        $expectedIds = array_slice($this->wishIds, $page * $limit, $limit);
        static::assertEquals($expectedIds, array_keys($wishes));

        foreach ($wishes as $wish) {
            static::assertInstanceOf(ListWishDto::class, $wish);
        }
    }

    public function testAddNewWish()
    {
        $repository = new WishRepository();
        $wishlist = new Wishlist($repository, new Currency('USD'));
        $dto = new NewWishDto();
        $dto->name = 'Foo Bar';
        $dto->price = 1000;
        $dto->fee = 10;
        $dto->initialFund = 10;
        $dto->isPublished = true;

        $wishId = $wishlist->addNewWish($dto);
        $typedWishId = WishId::fromString($wishId);

        static::assertTrue($repository->containsId($typedWishId));

        $wish = $repository->get($typedWishId);
        static::assertEquals($dto->name, $wish->getName());
        static::assertEquals($dto->price, $wish->getPrice()->getAmount());
        static::assertEquals($dto->fee, $wish->getFee()->getAmount());
        static::assertEquals($dto->initialFund, $wish->getFund()->getAmount());
        static::assertEquals($dto->isPublished, $wish->isPublished());
    }

    public function testDeposit()
    {
        $repository = new WishRepository($this->createWishes());
        $repositoryCapacity = $repository->count();
        $wishlist = new Wishlist($repository, new Currency('USD'));

        $deposit = $wishlist->deposit($this->wishIds[0]->getId(), 100);

        static::assertSame($repositoryCapacity, $repository->count());
        static::assertSame(
            $deposit->depositId,
            $repository->get($this->wishIds[0])->getDeposits()[0]->getId()->getId()
        );

        static::assertEquals(110, $repository->get($this->wishIds[0])->getFund()->getAmount());
    }

    public function testWithdraw()
    {
        $repository = new WishRepository($this->createWishes());
        $repositoryCapacity = $repository->count();
        $wishlist = new Wishlist($repository, new Currency('USD'));
        $wishId = $this->wishIds[0]->getId();
        $deposit = $wishlist->deposit($wishId, 25);

        $amount = $wishlist->withdraw($wishId, $deposit->depositId);

        static::assertSame($repositoryCapacity, $repository->count());
        static::assertTrue($amount->equals($repository->get($this->wishIds[0])->getFund()));
        static::assertEquals(10, $amount->getAmount());
    }

    public function testUnpublish()
    {
        $repository = new WishRepository($this->createWishes());
        $repositoryCapacity = $repository->count();
        $wishlist = new Wishlist($repository, new Currency('USD'));

        $wishlist->unpublish($this->wishIds[0]->getId());

        static::assertSame($repositoryCapacity, $repository->count());
        static::assertFalse($repository->get($this->wishIds[0])->isPublished());
    }

    public function testPublish()
    {
        $repository = new WishRepository($this->createWishes());
        $repositoryCapacity = $repository->count();
        $wishlist = new Wishlist($repository, new Currency('USD'));

        $wishlist->unpublish($this->wishIds[0]->getId());
        $wishlist->publish($this->wishIds[0]->getId());

        static::assertSame($repositoryCapacity, $repository->count());
        static::assertTrue($repository->get($this->wishIds[0])->isPublished());
    }

    private function createWishes(): array
    {
        $wishes = [];

        foreach (range(0, $this->wishIdsNumber - 1) as $index) {
            $wish = new Wish(
                $this->wishIds[$index],
                new WishName('Qux'),
                Expense::fromCurrencyAndScalars(
                    new Currency('USD'),
                    ($index + 1) * 100,
                    ($index + 1) * 5,
                    ($index + 1) * 10
                )
            );

            $wish->publish();

            $wishes[$this->wishIds[$index]->getId()] = $wish;
        }

        return $wishes;
    }
}
