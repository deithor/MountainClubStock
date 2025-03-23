<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BasketItem;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(
    routePath: '/',
    routeName: 'dashboard',
)]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(ItemCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Учёт снаряжения');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Главная страница', 'fa fa-home'),
            MenuItem::linkToCrud('Категории', 'fa fa-table-list', Category::class),
            MenuItem::linkToCrud('Предметы', 'fa fa-tent', Item::class),
            MenuItem::linkToCrud('Пользователи', 'fa fa-users', User::class),
            MenuItem::linkToCrud('Корзина', 'fa fa-cart-shopping', BasketItem::class),
        ];
    }
}
