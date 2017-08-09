<?php

namespace Wishlist\Tests\Http\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    public function testIndexActionShouldShowVueTemplate()
    {
        $client = $this->makeClient();

        $crawler = $client->request('GET', '/wishes');

        $this->assertStatusCode(200, $client);
        $this->assertThereIsOnlyOneWishlist($crawler);
    }

    private function assertThereIsOnlyOneWishlist(Crawler $crawler): void
    {
        static::assertEquals(1, $crawler->filter('.js-wishlist')->count());
    }

    /**
     * @param array $parameters
     * @dataProvider queryParametersDataProvider
     */
    public function testIndexActionShouldReturnJsonOnAjaxCall(array $parameters)
    {
        $limit = $parameters['limit'] ?? 10;
        $client = $this->makeClient();

        $client->request(
            'GET',
            '/wishes',
            $parameters,
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $response = $client->getResponse();

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $response);

        $actual = $this->parseJson($response);

        foreach (['wishes', 'pagination'] as $key) {
            static::assertArrayHasKey($key, $actual);
        }

        static::assertCount($limit, $actual['wishes']);

        $paginationKeys = [
            'page',
            'limit',
            'startIndex',
            'endIndex',
            'total',
            'totalPages'
        ];

        foreach ($paginationKeys as $key) {
            static::assertArrayHasKey($key, $actual['pagination']);
        }

        $this->assertOrderedByDate(array_column($actual['wishes'], 'id'), $limit);
    }

    public function queryParametersDataProvider()
    {
        return [
            'Should show latest 10 wishes' => [[]],
            'Should also show the same (latest) 10 wishes' => [['page' => 1]],
            'Should show given number of wishes on the given page' => [['page' => 1, 'limit' => 15]]
        ];
    }

    private function assertOrderedByDate($wishIds, $limit = 10)
    {
        static::assertEquals($this->getFixtureWishIds(0, $limit), $wishIds);
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
        $response = $client->getResponse();

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(
            [
                'url' => $this->router->generate('wishlist.wish.unpublish', compact('wishId')),
                'label' => $this->translator->trans('wishlist.table.unpublish'),
                'published' => true
            ],
            $this->parseJson($response)
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
        $response = $client->getResponse();

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(
            [
                'url' => $this->router->generate('wishlist.wish.publish', compact('wishId')),
                'label' => $this->translator->trans('wishlist.table.publish'),
                'published' => false
            ],
            $this->parseJson($response)
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

    public function testSimpleDeposit()
    {
        $client = $this->makeClient();

        $this->sendDepositRequest(
            $client,
            $this->fixtures['wish-0']->getId()->getId(),
            335
        );

        $response = $client->getResponse();

        $this->assertStatusCode(200, $client);
        static::assertInstanceOf(JsonResponse::class, $response);

        $json = $this->parseJson($response);
        static::assertArrayHasKey('success', $json);
        static::assertTrue($json['success']);
        static::assertArrayHasKey('deposit', $json);

        $depositKeys = [
            'depositId',
            'amount',
            'currency',
            'createdAt'
        ];

        foreach ($depositKeys as $key) {
            static::assertArrayHasKey($key, $json['deposit']);
        }
    }

    public function testDepositShouldFailValidation()
    {
        $client = $this->makeClient();

        $this->sendDepositRequest(
            $client,
            $this->fixtures['wish-0']->getId()->getId(),
            'nonsense'
        );

        $response = $client->getResponse();
        $json = $this->parseJson($response);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $client);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertArrayHasKey('success', $json);
        static::assertFalse($json['success']);
        static::assertArrayHasKey('violations', $json);
        static::assertArrayHasKey('amount', $json['violations']);
    }

    public function testMustNotDepositToUnpublishedWish()
    {
        $client = $this->makeClient();

        $this->sendDepositRequest(
            $client,
            $this->fixtures['wish-unpublished']->getId()->getId(),
            123
        );

        $response = $client->getResponse();
        $json = $this->parseJson($response);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $client);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertArrayHasKey('success', $json);
        static::assertFalse($json['success']);
        static::assertArrayHasKey('message', $json);
    }

    public function testMustNotDepositToFulfilledWish()
    {
        $client = $this->makeClient();

        $this->sendDepositRequest(
            $client,
            $this->fixtures['wish-fulfilled']->getId()->getId(),
            999
        );

        $response = $client->getResponse();
        $json = $this->parseJson($response);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $client);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertArrayHasKey('success', $json);
        static::assertFalse($json['success']);
        static::assertArrayHasKey('message', $json);
    }

    private function sendDepositRequest(Client $client, string $wishId, $amount): void
    {
        $client->request(
            'POST',
            $this->router->generate('wishlist.wish.deposit', compact('wishId')),
            compact('amount'),
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    private function parseJson(Response $response)
    {
        return json_decode($response->getContent(), true);
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
