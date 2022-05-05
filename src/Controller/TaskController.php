<?php

namespace App\Controller;

use App\Entity\Task;
use App\Type\TaskFilterType;
use App\Type\TaskType;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Util\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     *
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render("task/create.html.twig", [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/tasks", name="task_list")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {

            $filter = $taskFilterForm->getData();
            if ($filter['isCompleted'] === null) {
                unset($filter['isCompleted']);
            }

            $tasks = $this->getDoctrine()->getRepository(Task::class)
                ->findBy($filter, [
                    'dueDate' => 'DESC'
                ]);

        } else {
            /** @var $tasks */
            $tasks = $this->getDoctrine()->getManager()
                ->getRepository(Task::class)
                ->findBy([], [
                    'dueDate' => 'DESC'
                ]);
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'filterForm' => $taskFilterForm->createView()
        ]);
    }

    /**
     * @Route("/tasks/{id}/complete", name="task_complete")
     * @return Response
     */
    public function complete($id): Response
    {
        /** @var Task $task */
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        if ($task === null) {
            return $this->render($this->createNotFoundException(sprintf("Task with id %s not found", $id)));
        }

        $task->setIsCompleted(true);

        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @return Response
     */
    public function delete($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em -> find(Task::class, $id);

        if (!$task) {
            throw $this->createNotFoundException('No task for id '.$id);
        }

        $em -> remove($task);
        $em -> flush();

        return $this -> redirectToRoute("task_list");
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @return Response
     */
    public function edit($id, Request $request): Response
    {
        $em = $this -> getDoctrine() -> getManager();
        $task = $em -> find(Task::class, $id);

        if (!$task) {
            throw $this->createNotFoundException('No task for id '.$id);
        }

        $taskEditForm = $this->createForm(TaskType::class);
        $taskEditForm -> handleRequest($request);

        if ($taskEditForm -> isSubmitted() && $taskEditForm -> isValid()) {
            $data = $taskEditForm -> getData();

            $task -> setName($data["name"]);
            $task -> setDescription($data["description"]);
            $task -> setRank($data["rank"]);
            $task -> setDueDate($data["dueDate"]);

            $em -> flush();

            return $this -> redirectToRoute("task_list");
        } else {
            $taskEditForm ->setData([
                'name' => $task->getName(),
                'description' => $task->getDescription(),
                'rank' => $task->getRank(),
                'dueDate' => $task->getDueDate(),

            ]);
            return $this -> render("task/edit.html.twig", [
                "form" => $taskEditForm -> createView(),
                "task_id" => $id,
            ]);
        }
    }
}