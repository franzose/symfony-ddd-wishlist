<?php

namespace Wishlist\Tests\Http\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wishlist\Domain\Wish;
use Wishlist\Infrastructure\Persistence\Doctrine\Fixture\LoadWishesData;

class WishlistControllerTest extends WebTestCase
{
    /**
     * @var array|Wish[]
     */
    private $fixtures;

    /**
     * @var ReferenceRepository
     */
    private $references;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setUp()
    {
        parent::setUp();

        $executor = $this->loadFixtures([
            LoadWishesData::class,
        ]);

        $this->references = $executor->getReferenceRepository();
        $this->fixtures = $this->references->getReferences();

        $container = $this->getContainer();
        $this->router = $container->get('router');
        $this->translator = $container->get('translator');
    }

    /**
     * @dataProvider queryParametersDataProvider
     */
    public function testIndexActionShouldShowLatest10Wishes(array $parameters)
    {
        $client = $this->makeClient();

        $crawler = $client->request('GET', '/wishes', $parameters);

        $this->assertStatusCode(200, $client);
        $this->assertThereIsOnlyOneWishlist($crawler);

        $wishElements = $crawler->filter('.js-wish');

        static::assertEquals(10, $wishElements->count());
        $this->assertOrderedByDate($wishElements->extract(['data-id']));
    }

    public function queryParametersDataProvider()
    {
        return [
            'Should show latest 10 wishes' => [[]],
            'Should also show the same (latest) 10 wishes' => [['page' => 1]]
        ];
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

    public function testPublishShouldReturn404IfWishDoesNotExist()
    {
        $client = $this->makeClient();

        $this->sendPublishRequest($client, 'nonsense');

        $this->assertStatusCode(404, $client);
    }

    public function testPublishShouldPublishTheWish()
    {
        $client = $this->makeClient();
        $fixtureKey = 'wish-unpublished';
        $wishId = $this->fixtures[$fixtureKey]->getId()->getId();

        $this->sendPublishRequest($client, $wishId);

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $client->getResponse());
        static::assertEquals(
            [
                'url' => $this->router->generate('wishlist.wish.unpublish', compact('wishId')),
                'label' => $this->translator->trans('wishlist.table.unpublish'),
                'published' => true
            ],
            $this->parseJson($client)
        );

        static::assertTrue($this->getFixtureReference($fixtureKey)->isPublished());
    }

    public function testUnpublishShouldReturn404IfWishDoesNotExist()
    {
        $client = $this->makeClient();

        $this->sendUnpublishRequest($client, 'nonsense');

        $this->assertStatusCode(404, $client);
    }

    public function testUnpublishShouldUnpublishTheWish()
    {
        $client = $this->makeClient();
        $fixtureKey = 'wish-0';
        $wishId = $this->fixtures[$fixtureKey]->getId()->getId();

        $this->sendUnpublishRequest($client, $wishId);

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $client->getResponse());
        static::assertEquals(
            [
                'url' => $this->router->generate('wishlist.wish.publish', compact('wishId')),
                'label' => $this->translator->trans('wishlist.table.publish'),
                'published' => false
            ],
            $this->parseJson($client)
        );

        static::assertFalse($this->getFixtureReference($fixtureKey)->isPublished());
    }

    private function sendPublishRequest(Client $client, string $wishId): void
    {
        $client->request(
            'PUT',
            $this->router->generate('wishlist.wish.publish', compact('wishId')),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest',]
        );
    }

    private function sendUnpublishRequest(Client $client, string $wishId): void
    {
        $client->request(
            'PUT',
            $this->router->generate('wishlist.wish.unpublish', compact('wishId')),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest',]
        );
    }

    /**
     * @param $client
     *
     * @return mixed
     */
    private function parseJson(Client $client)
    {
        return json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * @param string $name
     *
     * @return object|Wish
     */
    private function getFixtureReference(string $name): Wish
    {
        return $this->references->getReference($name);
    }
}
