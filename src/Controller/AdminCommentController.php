<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentController extends AbstractController
{
    /**
     * 
     * Permet d'afficher la liste des commentaires
     * 
     * @Route("/admin/commentaires", name="admin_commentaires_index")
     * @return Response
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Comment::class);

        $comments = $repo->findAll();

        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }
    /**
     * Permet de modifier un commentaire
     * 
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     * 
     * @return Response
     */

     public function edit(Comment $comment, Request $request){
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(AdminCommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "Le commentaire a bien été modifié !"
            );
            return $this->redirectToRoute('admin_comment_index');
        }

         return $this->render('admin/comment/edit.html.twig', [
             'comment' => $comment,
             'form' => $form->createView()
         ]);
     }
     /**
     * Permet de supprimer un commentaire
     * 
     * @Route("/admin/commentaire/{id}/delete", name="admin_commentaire_delete")
     * 
     * @return Response
     */
    public function delete (Comment $comment){
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "Le commentaire a bien été supprimée !"
        );
            return $this->redirectToRoute('admin_commentaires_index');
    }
}
