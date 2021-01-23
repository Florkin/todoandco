<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * HomeController constructor.
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $tasks = $this->taskRepository->findBy([
            'done' => false,
            'user' => $this->getUser()->getId(),
        ], ['createdAt' => 'DESC'], 6);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tasks' => $tasks
        ]);
    }
}
