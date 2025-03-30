<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Enum\UserRole;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
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
            ->setSearchFields(['name', 'category.name'])
            ->setEntityLabelInSingular('Предмет')
            ->setEntityLabelInPlural('Предметы');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            TextField::new('name', 'Название')
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 255,
                    ],
                ]),
            TextField::new('description', 'Описание')
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 511,
                    ],
                ]),
            AssociationField::new('category', 'Категория'),
            IntegerField::new('quantity', 'Количество')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                    ],
                ]),
            IntegerField::new('price', 'Стоимость')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                    ],
                ])
                ->onlyOnForms(),
            DateTimeField::new('deletedAt', 'Удалено')
                ->formatValue(function ($value) {
                    return $value ? $value->format('Y-m-d h:m:s') : 'Нет';
                })
                ->onlyOnDetail(),
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

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.deletedAt IS NULL');

        return $queryBuilder;
    }
}
