<?php

namespace Wishlist\Http\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wishlist\Application\WishlistInterface;

class WishlistController
{
    private $engine;
    private $router;
    private $translator;
    private $wishlist;

    public function __construct(
        EngineInterface $engine,
        RouterInterface $router,
        TranslatorInterface $translator,
        WishlistInterface $wishlist
    ) {
        $this->engine = $engine;
        $this->router = $router;
        $this->translator = $translator;
        $this->wishlist = $wishlist;
    }

    /**
     * @Route("/wishes", name="wishlist.index", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->getInt('page', 1) - 1;
        $limit = $request->query->getInt('limit', 10);
        $startIndex = $page * $limit + 1;
        $endIndex = $startIndex + $limit - 1;

        $wishes = $this->wishlist->getWishesByPage($page, $limit);
        $total = $this->wishlist->getTotalWishesNumber();

        return $this->engine->renderResponse(
            ':wishlist:index.html.twig',
            compact(
                'wishes',
                'total',
                'page',
                'startIndex',
                'endIndex'
            )
        );
    }

    /**
     * @Route("/wishes/{wishId}/publish", name="wishlist.wish.publish", methods={"PUT"})
     * @param string $wishId
     *
     * @return JsonResponse
     */
    public function publishAction(string $wishId)
    {
        $this->wishlist->publish($wishId);

        return new JsonResponse([
            'url' => $this->router->generate('wishlist.wish.unpublish', compact('wishId')),
            'label' => $this->translator->trans('wishlist.table.unpublish')
        ]);
    }

    /**
     * @Route("/wishes/{wishId}/unpublish", name="wishlist.wish.unpublish", methods={"PUT"})
     * @param string $wishId
     *
     * @return JsonResponse
     */
    public function unpublishAction(string $wishId)
    {
        $this->wishlist->unpublish($wishId);

        return new JsonResponse([
            'url' => $this->router->generate('wishlist.wish.publish', compact('wishId')),
            'label' => $this->translator->trans('wishlist.table.publish')
        ]);
    }
}
