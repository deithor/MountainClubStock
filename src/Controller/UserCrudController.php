<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Service\BasketItemService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AdminCrud(routePath: '/users', routeName: 'users')]
class UserCrudController extends AbstractCrudController
{
    private const ACTION_GIVE_BASKET_ITEMS = 'giveItemsToUser';

    public function __construct(
        private readonly BasketItemService $basketItemService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
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
            TextField::new('email', 'Почта'),
            ChoiceField::new('roles', 'Роли')
                ->setChoices([
                    'Обычный пользователь' => UserRole::USER,
                    'Завснар' => UserRole::STOREKEEPER,
                    'Казначей' => UserRole::PAYMASTER,
                    'Администратор' => UserRole::ADMIN,
                ])
                ->allowMultipleChoices(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE)
            ->add(
                Crud::PAGE_INDEX,
                Action::new(self::ACTION_GIVE_BASKET_ITEMS, 'Выдать снаряжение из корзины')
                    ->linkToCrudAction('giveItemsToUser')
            )
            ->setPermission(self::ACTION_GIVE_BASKET_ITEMS, UserRole::STOREKEEPER)
            ->setPermission(Action::NEW, UserRole::ADMIN)
            ->setPermission(Action::DELETE, UserRole::ADMIN)
//            ->setPermission(Action::EDIT, UserRole::ADMIN)
        ;
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
}
