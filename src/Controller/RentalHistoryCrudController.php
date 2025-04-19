<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RentalRecord;
use App\Enum\UserRole;
use App\Service\RentalHistoryService;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

// todo: change default actions
// show returned items
// disable delete
// restrict access to whole history for role user
#[AdminCrud(routePath: '/rental-history', routeName: 'rental-history')]
class RentalHistoryCrudController extends AbstractCrudController
{
    private const ACTION_TAKE_ITEMS_FROM_USER = 'takeItemsFromUserAction';

    private const ACTION_TAKE_ITEMS_FROM_USER_BATCH = 'takeItemsFromUserBatchAction';

    public function __construct(
        private readonly RentalHistoryService $rentalHistoryService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

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
            // todo disable deleted items
            AssociationField::new('item', 'Предмет')
                ->hideWhenUpdating(),
            AssociationField::new('borrower', 'У кого')
                ->hideWhenUpdating(),
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

    public function createEntity(string $entityFqcn): RentalRecord
    {
        $rentalRecord = new RentalRecord();
        $rentalRecord
            ->setLender($this->getUser())
            ->setBorrowedAt(new DateTimeImmutable());

        return $rentalRecord;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.returnedAt IS NULL');

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Изменить количество');
            })
            ->add(Crud::PAGE_INDEX, Action::new(self::ACTION_TAKE_ITEMS_FROM_USER, 'Списать предметы')
                ->linkToCrudAction('takeItemsFromUserAction'))
            ->addBatchAction(Action::new(self::ACTION_TAKE_ITEMS_FROM_USER_BATCH, 'Списать предметы')
                ->linkToCrudAction('takeItemsFromUserAction'))
            ->setPermission(self::ACTION_TAKE_ITEMS_FROM_USER, UserRole::STOREKEEPER)
            ->setPermission(self::ACTION_TAKE_ITEMS_FROM_USER_BATCH, UserRole::STOREKEEPER)
            ->setPermission(Action::NEW, UserRole::STOREKEEPER);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('item')
            ->add('borrower');
    }

    public function takeItemsFromUserAction(AdminContext $context): RedirectResponse
    {
        $rentalRecordIds = $context->getRequest()->get('batchActionEntityIds') ?? [$context->getRequest()->get('entityId')];

        array_walk($rentalRecordIds, function (&$rentalRecordId): void {
            $rentalRecordId = (int)$rentalRecordId;
        });

        $this->rentalHistoryService->takeItemsFromUser($rentalRecordIds);

        $url = $this->adminUrlGenerator
            ->setController(RentalHistoryCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
