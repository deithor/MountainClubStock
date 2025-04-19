<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BasketItem;
use App\Enum\UserRole;
use App\Service\BasketItemService;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

// todo global button to give all items to user
#[AdminCrud(routePath: '/basket-items', routeName: 'basket-items')]
#[IsGranted(UserRole::STOREKEEPER)]
class BasketItemCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly BasketItemService $basketItemService,
    ) {
    }

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
            IntegerField::new('quantity', 'Количество')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 1,
                    ],
                ]),
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
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.user = :user')
            ->setParameter('user', $this->getUser());

        return $queryBuilder;
    }
}
