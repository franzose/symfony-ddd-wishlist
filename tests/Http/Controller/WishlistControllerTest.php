<?php

namespace Wishlist\Tests\Http\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Wishlist\Domain\Wish;
use Wishlist\Infrastructure\Persistence\Doctrine\Fixture\LoadWishesData;

class WishlistControllerTest extends WebTestCase
{
    /**
     * @var array|Wish[]
     */
    private $fixtures;

    public function setUp()
    {
        parent::setUp();

        $executor = $this->loadFixtures([
            LoadWishesData::class,
        ]);

        $this->fixtures = $executor->getReferenceRepository()->getReferences();
    }

    public function testIndexActionShouldShowLatest10Wishes()
    {
        $client = $this->makeClient();

        $crawler = $client->request('GET', '/wishes');

        $this->assertStatusCode(200, $client);
        $this->assertThereIsOnlyOneWishlist($crawler);

        $wishElements = $crawler->filter('.js-wish');

        static::assertEquals(10, $wishElements->count());
        $this->assertOrderedByDate($wishElements->extract(['data-id']));
    }

    private function getFixtureWishIds(int $offset = null, int $limit = null): array
    {
        $fixtures = $this->fixtures;

        usort($fixtures, function (Wish $one, Wish $two) {
            return $one->getCreatedAt() < $two->getCreatedAt() ? 1 : -1;
        });

        $ids = array_map(function (Wish $wish) {
            return $wish->getId()->getId();
        }, $fixtures);

        if (null === $offset) {
            return $ids;
        }

        return array_slice($ids, $offset, $limit, true);
    }

    private function assertOrderedByDate($wishIds)
    {
        static::assertEquals($this->getFixtureWishIds(0, 10), $wishIds);
    }

    private function assertThereIsOnlyOneWishlist(Crawler $crawler): void
    {
        static::assertEquals(1, $crawler->filter('.js-wishlist')->count());
    }
}
