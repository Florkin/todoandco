<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Handler\Forms\EntityFormHandler;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private $taskRepository;
    /**
     * @var EntityFormHandler
     */
    private $formHandler;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TaskController constructor.
     * @param TaskRepository $taskRepository
     * @param EntityFormHandler $formHandler
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TaskRepository $taskRepository, EntityFormHandler $formHandler, EntityManagerInterface $entityManager)
    {
        $this->taskRepository = $taskRepository;
        $this->formHandler = $formHandler;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/tasks", name="task_index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $done = $request->get('done');
        $anonymous = $request->get('anonymous');
        $all = $request->get('all');
        // If request "all" or "anonymous", check if user is ADMIN
        if (((null !== $all && $all) || (null !== $anonymous && $anonymous)) && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisés à voir les tâches des autres utilisateurs");
        }

        return $this->render(
            'task/index.html.twig', [
            'tasks' => $this->taskRepository->findByUserQuery($this->getUser(), $done, $all, $anonymous)
        ]);
    }

    /**
     * @Route("/tasks/new", name="task_new")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function new(Request $request): Response
    {
        $task = new Task();
        $task->setDone(false);
        $task->setUser($this->getUser());
        if ($this->formHandler->handle($request, $task, TaskType::class)) {
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', ['form' => $this->formHandler->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param Task $task
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function edit(Task $task, Request $request)
    {
        $this->denyAccessUnlessGranted('TASK_EDIT', $task, "Vous n'avez pas le droit d'éditer cette tâche");
        if ($this->formHandler->handle($request, $task, TaskType::class)) {
            $this->addFlash('success', 'La tâche a été bien été modifiée.');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $this->formHandler->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @return RedirectResponse
     */
    public function toggleTaskAction(Task $task)
    {
        $this->denyAccessUnlessGranted('TASK_EDIT', $task, "Vous n'avez pas le droit d'éditer cette tâche");
        $task->setDone(!$task->isDone());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_index');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param Task $task
     * @return RedirectResponse
     */
    public function delete(Task $task)
    {
        $this->denyAccessUnlessGranted('TASK_DELETE', $task, "Vous n'avez pas le droit de supprimer cette tâche");
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_index');
    }
}
