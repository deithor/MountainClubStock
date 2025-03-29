<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/items', routeName: 'items')]
class ItemCrudController extends AbstractCrudController
{
    private const ACTION_ADD_TO_BASKET = 'addToBasket';

    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(30)
//            ->setEntityPermission('ROLE_EDITOR')
            ->setSearchFields(['name', 'category.name'])
            ->setEntityLabelInSingular('Предмет')
            ->setEntityLabelInPlural('Предметы');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            TextField::new('name', 'Название'),
            TextField::new('description', 'Описание'),
            AssociationField::new('category', 'Категория'),
            IntegerField::new('quantity', 'Количество'),
            IntegerField::new('price', 'Стоимость')
                ->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
//            ->add(
//                Crud::PAGE_INDEX,
//                Action::new(self::ACTION_ADD_TO_BASKET, 'Добавить в корзину')
//                    ->linkToCrudAction('addToBasket')
//            )
//            ->setPermission(self::ACTION_ADD_TO_BASKET, UserRole::STOREKEEPER)
            ->setPermission(Action::NEW, UserRole::STOREKEEPER)
            ->setPermission(Action::DELETE, UserRole::STOREKEEPER)
            ->setPermission(Action::EDIT, UserRole::STOREKEEPER)
            ->setPermission(Action::BATCH_DELETE, UserRole::STOREKEEPER);
    }
}
