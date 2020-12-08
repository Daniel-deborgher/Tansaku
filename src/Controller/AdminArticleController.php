<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminArticleController extends AbstractController
{
        /**
     * @Route("/admin/articles", name="admin_articles_index")
     */
    public function admin_show_articles(ArticleRepository $repo): Response
    {

        return $this->render('admin/article/index.html.twig', [
            'articles' => $repo->findAll(),
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route ("/admin/article/{slug}/edit", name="admin_article_edit")
     *
     * @return Response
     */
    public function edit(Article $article, Request $request){
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $entityManager->persist($article);
           $entityManager->flush();

           $this->addFlash(
               'success',
               "L'annonce a bien été modifié !"
           );
        }
        return $this-> render('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }
    /**
     * Permet de supprimer une annonce
     * 
     * @Route("/admin/article/{id}/delete", name="admin_article_delete")
     * 
     * @return Response
     */
    public function delete (Article $article){
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "L'annonce a bien été supprimée !"
        );
            return $this->redirectToRoute('admin_articles_index');
    }
}
