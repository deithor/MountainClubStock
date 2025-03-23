<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BasketItem;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

#[AdminCrud(routePath: '/basket-items', routeName: 'basket-items')]
class BasketItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BasketItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(30)
            ->setSearchFields(['item.name', 'category.name'])
            ->setEntityLabelInSingular('Предмет в корзине')
            ->setEntityLabelInPlural('Предметы в корзине');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            AssociationField::new('item', 'Предмет'),
            IntegerField::new('quantity', 'Количество'),
        ];
    }

    public function createEntity(string $entityFqcn): BasketItem
    {
        $basketItem = new BasketItem();
        $basketItem->setUser($this->getUser());

        return $basketItem;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $response = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->where("entity.user = {$this->getUser()->getId()}");

        return $response;
    }
}
