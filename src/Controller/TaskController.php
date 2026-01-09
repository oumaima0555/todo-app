<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/task')]
final class TaskController extends AbstractController
{
    #[Route('/', name: 'task_index', methods:['GET'])]
    public function index(TaskRepository $taskRepository, Request $request, ChartBuilderInterface $chartBuilder): Response
    {
        //Ajouter par SALMA deja fait non aujourd'hui
        //  Sécurité : utilisateur connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération des filtres
        $q = $request->query->get('q');
        $status = $request->query->get('status');
        // 🔁 TRI PAR DATE
        $sort = $request->query->get('sort', 'asc'); // asc | desc
        $direction = $sort === 'desc' ? 'DESC' : 'ASC';


        // Conversion du status en booléen ou null
        $statusBool = null;
        if ($status === 'done') {
            $statusBool = true;
        } elseif ($status === 'todo') {
            $statusBool = false;
        }

        //  Logique métier : recherche et filtres par salma
        $tasks = $taskRepository->findBySearchAndStatus(
        $this->getUser(),
        $q,
        $statusBool,
        $direction
);


        // Compteurs (Globaux)
        // Note : Idéalement faire des requêtes COUNT en DB pour la perf, mais ici on reste simple
        $allTasks = $taskRepository->findBy(['user' => $this->getUser()]);
        $total = count($allTasks);
        $done = count(array_filter($allTasks, fn($t) => $t->isStatus() === true));
        $todo = $total - $done;

        // Fin SALMA

        // --- CHART GENERATION ---
        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chart->setData([
            'labels' => ['Terminées', 'À faire'],
            'datasets' => [
                [
                    'backgroundColor' => ['#10b981', '#f59e0b'],
                    'borderColor' => ['transparent', 'transparent'],
                    'data' => [$done, $todo],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'color' => '#6b7280',
                        'font' => ['family' => 'Outfit']
                    ]
                ]
            ],
            'cutout' => '70%',
        ]);
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'counters' => [
                'total' => $total,
                'done' => $done,
                'todo' => $todo
            ],
            'chart' => $chart,
            'searchParams' => [
                'q' => $q,
                'status' => $status
            ]
        ]);
    }


    #[Route('/new', name: 'task_new', methods:['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {

       //  sécurité : utilisateur connecté
       $this->denyAccessUnlessGranted('ROLE_USER');

       //  LIGNE OBLIGATOIRE (LE PROBLÈME ÉTAIT ICI)
       $task->setUser($this->getUser());

       // (optionnel si déjà dans le constructeur)
       $task->setCreatedAt(new \DateTime());

       $em->persist($task);
       $em->flush();

       return $this->redirectToRoute('task_index');
}


        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'task_show', requirements: ['id' => '\d+'], methods:['GET'])]
    public function show(Task $task): Response
    {
        // Ajouter par SALMA
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($task->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        // Fin SALMA
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/toggle', name: 'task_toggle', methods:['POST'])]
    public function toggle(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($task->getUser() !== $this->getUser()) {
             return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $task->setStatus(!$task->isStatus());
        $em->flush();

        return new JsonResponse([
            'status' => $task->isStatus(),
            'message' => 'Task updated'
        ]);
    }

    #[Route('/{id}/edit', name: 'task_edit', methods:['GET','POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $em): Response
    {
        // Ajouter par SALMA
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($task->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        // Fin SALMA
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'task_delete', methods:['POST'])]
  public function delete(Request $request, Task $task, EntityManagerInterface $em): Response
  {
    // Sécurité utilisateur
    $this->denyAccessUnlessGranted('ROLE_USER');
    if ($task->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

    // ❌ BLOQUER SI TÂCHE TERMINÉE
    if ($task->isStatus()) {
        $this->addFlash('warning', 'Impossible de supprimer une tâche terminée.');
        return $this->redirectToRoute('task_index');
    }

    if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
        $em->remove($task);
        $em->flush();
    }

    return $this->redirectToRoute('task_index');
}


    // ==================== ROUTES DE DEBUG ====================
    
    #[Route('/debug/insert', name: 'task_debug_insert')]
    public function debugInsert(EntityManagerInterface $em): Response
    {
        // Test 1: Insertion directe sans formulaire
        //AJOUTER PAR SALMA : sécurité utilisateur connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        $task = new Task();
        $task->setTitle('Test Debug ' . date('H:i:s'));
        $task->setDescription('Test insertion directe');
        $task->setStatus(false);
        $task->setCreatedAt(new \DateTime()); // garde si pas de constructeur

        $task->setUser($this->getUser()); //  OUI obligatoire

        $em->persist($task);
        $em->flush();

         $id = $task->getId();

        
        // Test 2: Lecture de toutes les tâches
        $allTasks = $em->getRepository(Task::class)->findAll();
        
        return $this->render('task/debug.html.twig', [
            'id' => $id,
            'tasks' => $allTasks
        ]);
    }
    
    #[Route('/debug/check-db', name: 'task_check_db')]
    public function checkDb(EntityManagerInterface $em): Response
    {
        $connection = $em->getConnection();
        
        try {
            // Test connexion
            $connection->executeQuery('SELECT 1');
            
            // Vérifie si la table existe
            $tables = $connection->fetchAllAssociative("SHOW TABLES LIKE 'task'");
            $tableExists = count($tables) > 0;
            
            // Compte les tâches
            $taskCount = 0;
            if ($tableExists) {
                $taskCount = $connection->fetchOne("SELECT COUNT(*) FROM task");
            }
            
            // Structure de la table
            $structure = [];
            if ($tableExists) {
                $structure = $connection->fetchAllAssociative("DESCRIBE task");
            }
            
            // Toutes les tâches
            $allTasks = [];
            if ($tableExists) {
                $allTasks = $em->getRepository(Task::class)->findAll();
            }
            
            return $this->render('task/check_db.html.twig', [
                'tableExists' => $tableExists,
                'taskCount' => $taskCount,
                'structure' => $structure,
                'allTasks' => $allTasks
            ]);
            
        } catch (\Exception $e) {
            return new Response('❌ ERREUR DB: ' . $e->getMessage());
        }
    }
    
    #[Route('/debug/sql-test', name: 'task_sql_test')]
    public function sqlTest(EntityManagerInterface $em): Response
    {
        $connection = $em->getConnection();
        
        $output = "<h1>Test SQL Direct</h1>";
        
        try {
            // Test 1: Insertion SQL directe
            $connection->executeQuery("
                INSERT INTO task (title, description, status, created_at) 
                VALUES ('Test SQL Direct', 'Insertion via SQL pur', 1, NOW())
            ");
            
            $output .= "<p style='color: green;'>✅ Insertion SQL réussie</p>";
            
            // Test 2: Comptage
            $count = $connection->fetchOne("SELECT COUNT(*) FROM task");
            $output .= "<p>Nombre de tâches: $count</p>";
            
            // Test 3: Affichage
            $tasks = $connection->fetchAllAssociative("SELECT * FROM task ORDER BY id DESC LIMIT 5");
            $output .= "<h3>Dernières tâches:</h3>";
            $output .= "<ul>";
            foreach ($tasks as $task) {
                $output .= "<li>#{$task['id']} - {$task['title']} ({$task['created_at']})</li>";
            }
            $output .= "</ul>";
            
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
        }
        
        $output .= "<br><a href='/task/'>Retour à la liste</a>";
        
        return new Response($output);
    }
}