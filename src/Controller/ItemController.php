<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ItemRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/items')]
class ItemController extends AbstractController
{
    private const PAGE_ITEM_LIMIT = 20;

    #[Route(
        path: '/',
        name: 'get_items_list',
        methods: ['GET']
    )]
    public function getList(
        ItemRepository $itemRepository,
        PaginatorInterface $paginator,
        Request $request,
    ): Response {
        $params = [];

        if ($request->query->get('name')) {
            $params['name'] = $request->query->get('name');
        }

        if ($request->query->get('category')) {
            $params['category'] = $request->query->get('category');
        }

        $pagination = $paginator->paginate(
            $itemRepository->getQueryForList($params),
            $request->query->getInt('page', default: 1),
            self::PAGE_ITEM_LIMIT
        );

        return $this->render('item/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
