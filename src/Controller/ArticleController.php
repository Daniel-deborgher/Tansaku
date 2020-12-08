<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * Permet d'afficher tous les articles
     * @Route("/actu", name="article_index")
     * @return Response
     */
    public function index(Request $request, PaginatoRInterface $paginator)
    {    
        $donnees = $this->getDoctrine()->getRepository(Article::class)->findBy([],['createdAt' => 'desc']);

        $articles = $paginator->paginate(
            $donnees, // on lui passe nos données
            $request->query->getInt('page', 1), // Numéro de la page en cours, 1 par défaut
            6
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles
        ]);
    }
    
    /**
     * Permet d'afficher un article
     * 
     * @Route ("/actu/{slug}", name="show_article")
     * 
     * @return  Response
     */
    public function show_article($slug, ArticleRepository $repo, Request $request, Article $article){


        $entityManager = $this->getDoctrine()->getManager();
        

        $comment = new Comment;

        $form = $this->createform(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article)
                    ->setAuthor($this->getUser());
            
            $entityManager->persist($comment);
            $chaine1 = $comment->getContent();
            $chaine2 = 'delete';
            $chaine3 = 'DELETE';
            $chaine4 = 'truncate';
            $chaine5 = 'TRUNCATE';
            $chaine6 = 'select';
            $chaine7 = 'SELECT';

            if(strstr($chaine1, $chaine2)) {
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            elseif(strstr($chaine1, $chaine3)){
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            elseif(strstr($chaine1, $chaine4)){
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            elseif(strstr($chaine1, $chaine5)){
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            elseif(strstr($chaine1, $chaine6)){
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            elseif(strstr($chaine1, $chaine7)){
                $this->addFlash(
                    'success',
                    'Votre commentaire a bien été pris en compte !'
                );
            }

            else {
                 $entityManager->flush();
                 
                 $this->addFlash(
                'success',
                'Votre commentaire a bien été pris en compte !'
            );
            }
            
        }

        // Je récupère l'article qui correspond au slug
        $article = $repo->findOneBySlug($slug);
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
            'comments' =>$comment
        ]);
    }
    
}
