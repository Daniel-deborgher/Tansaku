<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * Permet d'afficher le dashboard
     * 
     * @Route("/admin", name="admin_dashboard")
     * 
     * @return Response
     */
    public function index(EntityManagerInterface $entityManager)
    {
        
        $users = $entityManager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
        $articles = $entityManager->createQuery('SELECT COUNT(a) FROM App\Entity\Article a')->getSingleScalarResult();
        $comments = $entityManager->createQuery('SELECT COUNT(c) FROM App\Entity\Comment c')->getSingleScalarResult();

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => 
                compact('users', 'articles', 'comments')
            ]);
    }
}
