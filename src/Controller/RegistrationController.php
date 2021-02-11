<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Handler\Forms\UserFormHandler;
use App\Security\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @param GuardAuthenticatorHandler $guardAuthenticatorHandler
     * @param Authenticator $authenticator
     * @param UserFormHandler $formHandler
     * @return Response
     * @Route("/register", name="app_register")
     * @Route("/admin/users/new", name="user_new")
     */
    public function register(Request $request,
                             GuardAuthenticatorHandler $guardAuthenticatorHandler,
                             Authenticator $authenticator,
                             UserFormHandler $formHandler): Response
    {
        $user = new User();
        if ($formHandler->handle($request, $user, UserType::class)) {
            if (!$this->getUser()) {
                return $guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main' // firewall name in security.yaml
                );
            }

            $this->addFlash('success', 'l\'utilisateur a bien été ajouté');
            return $this->redirectToRoute('homepage');
        };

        return $this->render('registration/register.html.twig', [
            'form' => $formHandler->createView(),
        ]);
    }
}
