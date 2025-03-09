<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Repository\BasketItemRepository;
use App\Service\BasketItemService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/basket_items')]
class BasketItemController extends AbstractController
{
    private const PAGE_LIMIT = 20;

    #[Route(
        path: '/{id<\d+>}/add',
        name: 'create_basket_item',
        methods: ['POST'],
    )]
    public function create(
        Item $item,
        BasketItemService $basketItemService,
        int $quantity = 1,
    ): Response {
        $basketItemService->createBasketItem($this->getUser(), $item, $quantity);

        return $this->redirectToRoute('get_items_list');
    }

    #[Route(
        path: '/',
        name: 'get_basket_item_list',
        methods: ['GET'],
    )]
    public function list(
        BasketItemRepository $basketItemRepository,
        PaginatorInterface $paginator,
        Request $request,
    ): Response {
        $pagination = $paginator->paginate(
            $basketItemRepository->getQueryForList($this->getUser()),
            $request->query->getInt('page', default: 1),
            self::PAGE_LIMIT
        );

        return $this->render('basket/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
