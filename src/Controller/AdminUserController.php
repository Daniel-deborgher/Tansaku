<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    /**
     * 
     * Permet d'afficher la liste des utilisateurs
     * 
     * @Route("/admin/users", name="admin_users_index")
     * 
     * @return Response
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(User::class);

        $users = $repo->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }
     /**
     * Permet de supprimer un utilisateurs
     * 
     * @Route("/admin/user/{id}/delete", name="admin_user_delete")
     * 
     * @return Response
     */
    public function delete (User $user){
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur a bien été supprimée !"
        );
            return $this->redirectToRoute('admin_users_index');
    }
}
