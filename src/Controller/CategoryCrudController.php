<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/categories', routeName: 'categories')]
class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(30)
            ->setSearchFields(['name'])
            ->setEntityLabelInSingular('Категория')
            ->setEntityLabelInPlural('Категории');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            TextField::new('name', 'Название')
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 40,
                    ],
                ]),
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
