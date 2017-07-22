<?php

namespace Wishlist\Http\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wishlist\Application\WishlistInterface;

class WishlistController
{
    private $engine;
    private $wishlist;
    private $validator;

    public function __construct(
        EngineInterface $engine,
        WishlistInterface $wishlist,
        ValidatorInterface $validator
    ) {
        $this->engine = $engine;
        $this->wishlist = $wishlist;
        $this->validator = $validator;
    }

    /**
     * @Route("/wishes", name="wishlist.index", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->getInt('page', 0);
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
                'startIndex',
                'endIndex'
            )
        );
    }
}
