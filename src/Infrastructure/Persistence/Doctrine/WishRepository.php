<?php

namespace Wishlist\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Wishlist\Domain\Exception\WishNotFoundException;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishRepositoryInterface;

final class WishRepository implements WishRepositoryInterface
{
    private $manager;
    /** @var EntityRepository */
    private $wishes;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->wishes  = $this->manager->getRepository(Wish::class);
    }

    public function get(WishId $wishId): Wish
    {
        $wish = $this->wishes
            ->createQueryBuilder('wish')
            ->select(['wish', 'deposits'])
            ->leftJoin('wish.deposits', 'deposits')
            ->andWhere('wish.id = :id')
            ->setParameter('id', $wishId)
            ->getQuery()
            ->getSingleResult();

        if (null === $wish) {
            throw new WishNotFoundException($wishId);
        }

        return $wish;
    }

    public function put(Wish $wish)
    {
        $this->manager->persist($wish);
        $this->manager->flush();
    }

    public function slice(int $offset, int $limit): array
    {
        $query = $this->wishes
            ->createQueryBuilder('wish')
            ->select(['wish', 'deposits'])
            ->leftJoin('wish.deposits', 'deposits')
            ->orderBy('wish.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return (new Paginator($query, true))
            ->getIterator()
            ->getArrayCopy();
    }

    public function contains(Wish $wish): bool
    {
        return $this->containsId($wish->getId());
    }

    public function containsId(WishId $wishId): bool
    {
        return null !== $this->manager
            ->createQueryBuilder()
            ->select('wish.id')
            ->from('Wishlist:Wish', 'wish')
            ->andWhere('wish.id = :id')
            ->setParameter('id', $wishId)
            ->getQuery()
            ->getSingleResult();
    }

    public function count(): int
    {
        return $this->manager
            ->createQueryBuilder()
            ->select('count(wish)')
            ->from('Wishlist:Wish', 'wish')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNextWishId(): WishId
    {
        return WishId::next();
    }
}
