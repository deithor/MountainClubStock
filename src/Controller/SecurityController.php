<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,
            'translation_domain' => 'admin',
            'favicon_path' => '/favicon-admin.svg',

            'page_title' => 'Авторизация',
            'csrf_token_intention' => 'authenticate',

            'target_path' => $this->generateUrl('dashboard'),
            'username_label' => 'Логин',
            'password_label' => 'Пароль',
            'sign_in_label' => 'Войти',

            // todo add reset password form
//            'forgot_password_enabled' => true,
//             the path (i.e. a relative or absolute URL) to visit when clicking the "forgot password?" link (default: '#')
//            'forgot_password_path' => $this->generateUrl('...', ['...' => '...']),
//            'forgot_password_label' => 'Забыли пароль?',

            'remember_me_enabled' => true,
            'remember_me_checked' => true,
            'remember_me_label' => 'Запомнить',
        ]);
    }
}
