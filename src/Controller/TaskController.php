<?php

namespace App\Controller;

use App\Entity\Task;
use App\Type\TaskFilterType;
use App\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/create", name="task_create")
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
     * @param $request
     * @return Response
     */
    public function list(Request $request): Response
    {

        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid())
        {
            $filter = $taskFilterForm->getData();

            if($filter['isCompleted'] === null)
            {
                unset($filter['isCompleted']);
            }

            $tasks = $this->getDoctrine()
                ->getRepository(Task::class)
                ->findBy($filter, [
                    'dueDate' => 'ASC',
                ]);
        }
        else
        {
            $tasks = $this->getDoctrine()
                ->getManager()
                ->getRepository(Task::class)
                ->findBy([], [
                    'dueDate' => 'ASC'
                ]);
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'filterForm' => $taskFilterForm->createView(),
        ]);
    }

    /**
     * @Route("/tasks/{id}/complete", name="task_complete")
     * @return \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function complete($id)
    {
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);
        if ($task === null)
        {
            return $this->createNotFoundException(sprintf("Not found id %s", $id));
        }

        $task->setIsCompleted(true);
        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("task_list");
    }

    /**
     * @Route("/tasks/{id}/undo", name="task_undo")
     * @return Response
     */
    public function destroy($id)
    {
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);
        if ($task === null)
        {
            return $this->createNotFoundException(sprintf("Not found id %s", $id));
        }

        $task->setIsCompleted(false);
        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("task_list");
    }
}