<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Handler\Forms\UserFormHandler;
use App\Handler\PaginatorHandler;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var PaginatorHandler
     */
    private $pager;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param PaginatorHandler $pager
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, PaginatorHandler $pager)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->pager = $pager;
    }

    /**
     * @Route("/admin/users", name="user_index")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(UserRepository $userRepository, Request $request)
    {
        $query = $userRepository->findAllQuery();
        return $this->render('user/index.html.twig', ['users' => $this->pager->paginate($request, $query)]);
    }

    /**
     * @Route("/admin/users/new", name="user_new")
     * @param Request $request
     * @param UserFormHandler $formHandler
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, UserFormHandler $formHandler)
    {
        $user = new User;
        if ($formHandler->handle($request, $user,UserType::class)) {
            $this->addFlash('success', "L'utilisateur a bien été ajouté");
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', ['form' => $formHandler->createView(), 'user' => $user]);
    }

    /**
     * @Route("users/{id}/edit", name="user_edit")
     * @param User $user
     * @param Request $request
     * @param UserFormHandler $formHandler
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(User $user, Request $request, UserFormHandler $formHandler)
    {
        $this->denyAccessUnlessGranted('USER_EDIT', $user);

        if ($formHandler->handle($request, $user,UserType::class)) {
            $this->addFlash('success', "L'utilisateur a bien été modifié");
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('user_index');
            }
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/edit.html.twig', ['form' => $formHandler->createView(), 'user' => $user]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     * @param User $user
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(User $user, Request $request, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('USER_DELETE', $user);
        if ($this->isCsrfTokenValid("delete" . $user->getId(), $request->get("_token"))) {
            if ($this->getUser()->getId() === $user->getId()) {
                $session->invalidate();
            }
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été supprimée.');
            return $this->redirectToRoute('user_index');
        }
        $this->addFlash('error', 'Token invalide');
        return $this->redirectToRoute('user_index');
    }
}
