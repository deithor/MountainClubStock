<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Service\BasketItemService;
use Closure;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AdminCrud(routePath: '/users', routeName: 'users')]
class UserCrudController extends AbstractCrudController
{
    private const ACTION_GIVE_BASKET_ITEMS = 'giveItemsToUser';

    private const ACTION_SHOW_RENTAL_HISTORY = 'showRentalHistoryAction';

    public function __construct(
        private readonly BasketItemService $basketItemService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        public readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(30)
            ->setSearchFields(['email'])
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            TextField::new('username', 'Логин')
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 180,
                    ],
                ]),
            TextField::new('email', 'Почта')
                ->setFormType(EmailType::class)
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 180,
                    ],
                ]),
            ChoiceField::new('roles', 'Роли')
                ->setChoices([
                    'Обычный пользователь' => UserRole::USER,
                    'Завснар' => UserRole::STOREKEEPER,
                    'Казначей' => UserRole::PAYMASTER,
                    'Администратор' => UserRole::ADMIN,
                ])
                ->allowMultipleChoices(),
            TextField::new('password', 'Пароль')
                ->setFormType(PasswordType::class)
                ->onlyWhenCreating(),
            DateTimeField::new('deletedAt', 'Удалено')
                ->formatValue(function ($value) {
                    return $value ? $value->format('Y-m-d h:m:s') : 'Нет';
                })
                ->onlyOnDetail(),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder
            ->andWhere('entity.deletedAt IS NULL');

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $showRentalHistory = Action::new(self::ACTION_SHOW_RENTAL_HISTORY, 'Посмотреть снаряжение на руках')
            ->linkToCrudAction('showRentalHistoryAction');

        return $actions
            ->disable(Action::BATCH_DELETE)
            ->add(
                Crud::PAGE_INDEX,
                Action::new(self::ACTION_GIVE_BASKET_ITEMS, 'Выдать снаряжение из корзины')
                    ->linkToCrudAction('giveItemsToUser')
            )
            ->add(Crud::PAGE_INDEX, $showRentalHistory)
            ->add(Crud::PAGE_DETAIL, $showRentalHistory)
            ->setPermission(self::ACTION_GIVE_BASKET_ITEMS, UserRole::STOREKEEPER)
            ->setPermission(Action::NEW, UserRole::ADMIN)
            ->setPermission(Action::DELETE, UserRole::ADMIN)
            ->setPermission(Action::EDIT, UserRole::ADMIN);
    }

    public function giveItemsToUser(AdminContext $context): RedirectResponse
    {
        if (!$context->getRequest()->get('entityId')) {
            $this->addFlash('warning', 'Укажите пользователя');

            $url = $this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        $userId = (int)$context->getRequest()->get('entityId');

        try {
            $this->basketItemService->giveItemsToUser($userId);
        } catch (BadRequestHttpException | NotFoundHttpException $exception) {
            $this->addFlash('warning', "{$exception->getMessage()}");

            $url = $this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        // todo: change redirect to rental history for user
        $url = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($userId)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword(): Closure
    {
        return function ($event): void {
            $form = $event->getForm();

            if (!$form->isValid()) {
                return;
            }

            $password = $form->get('password')->getData();

            if ($password === null) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($this->getUser(), $password);
            $form->getData()->setPassword($hash);
        };
    }

    public function showRentalHistoryAction(AdminContext $context): RedirectResponse
    {
        $url = $this->adminUrlGenerator
            ->setController(RentalHistoryCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[borrower][value]', $context->getRequest()->get('entityId'))
            ->set('filters[borrower][comparison]', '=')
            ->generateUrl();

        return $this->redirect($url);
    }
}
