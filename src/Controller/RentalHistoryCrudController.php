<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RentalRecord;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

// todo: change default actions
#[AdminCrud(routePath: '/rental-history', routeName: 'rental-history')]
class RentalHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RentalRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        // todo: add search
        return $crud
            ->setPaginatorPageSize(30)
            ->setEntityLabelInSingular('Предмет на руках')
            ->setEntityLabelInPlural('Предметы на руках');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            AssociationField::new('item', 'Предмет'),
            AssociationField::new('borrower', 'У кого'),
            IntegerField::new('quantity', 'Количество')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 1,
                    ],
                ]),
            AssociationField::new('lender', 'Кто выдал')
                ->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function createEntity(string $entityFqcn): RentalRecord
    {
        $rentalRecord = new RentalRecord();
        $rentalRecord->setLender($this->getUser());

        return $rentalRecord;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $response = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->where('entity.returnedAt IS NULL');

        return $response;
    }
}
