<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BlogController extends AbstractController
{
     /**
     * @Route("/", name="home")
     */
    public function home(ArticleRepository $repo)
    {
        return $this->render('blog/home.html.twig', [
            'actu' => $repo->findByLast(3),
            'last_manga' => $repo->findByLastCategory(3),
            'anime' => $repo->findByLastCategory(4),
            'jeux_video' => $repo->findByLastCategory(5),
            'a_decouvrir' => $repo->findByLastCategory(6)
        ]);
    }

     /**
     * @Route("/politique-de-confidentialite", name="policity")
     */
    public function policity_privacy(): Response
    {
        return $this->render('blog/en_savoir_plus.html.twig');
    }

    /**
     * @Route("blog/actu/new", name="create_article")
     * @Security("is_granted('ROLE_EDITOR')", message="Cet accès est exclusivement réservé à notre équipe")
     *
     * @return Response
     */
    public function form_article(Article $article = null,Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        if(!$article){
            $article = new Article; 
        }    
        $form = $this->createFormBuilder($article)
                        ->add('auteur')
                        ->add('titre')
                        ->add('slug')
                        ->add('description')
                        ->add('contenu')
                        ->add('image', FileType::class, [
                            "mapped" => false,
                            "constraints" => [
                                new File([
                                    "mimeTypes" => ["image/jpeg", "image/png"],
                                    "mimeTypesMessage" => "Formats autorisés : jpg, png",
                                    "maxSize" => "2048k",
                                    "maxSizeMessage" => "le fichier ne doit pas dépasser 2mo"
                                    ])
                            ]
                        ])
                        ->add('category', EntityType::class, [
                            'class' => Category::class,
                            'choice_label' => 'titre',
                        ])
                        ->getForm();
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            if(!$article->getId()){
               $article->setCreatedAt(new \DateTime()); 
            }
            $fichier = $form->get("image")->getData();
            
            if($fichier){
                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nomFichier .= uniqid();
                $nomFichier .= "." . $fichier->guessExtension();
                $nomFichier = str_replace(" ", "_", $nomFichier);
                //on enregistre le fichier téléchargé dans un dossier public/images
                $dossier = $this->getParameter("dossier_images");
                $fichier->move($dossier, $nomFichier);
                $article->setImage($nomFichier);
            }
            
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Votre article a été ajouté !'
            );

            return $this->redirectToRoute("show_article", [
                'slug' => $article->getSlug()
            ]);
        }

        return $this->render('blog/create.article.html.twig',
        [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
            
        ]);
    }   
     /**
     * @Route("blog/actu/{slug}/edit", name="edit_article", methods={"GET","POST"})
     * @Security("is_granted('ROLE_EDITOR')", message="Cet accès est exclusivement réservé à notre équipe")
     *
     * @return Response
     */
    public function form_article_edit(Article $article,Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder($article)
                        ->add('auteur')
                        ->add('titre')
                        ->add('slug')
                        ->add('description')
                        ->add('contenu')
                        ->add('image', FileType::class, [
                            "mapped" => false,
                            "constraints" => [
                                new File([
                                    "mimeTypes" => ["image/jpeg", "image/png"],
                                    "mimeTypesMessage" => "Formats autorisés : jpg, png",
                                    "maxSize" => "2048k",
                                    "maxSizeMessage" => "le fichier ne doit pas dépasser 2mo"
                                    ])
                            ]
                        ])
                        ->add('category', EntityType::class, [
                            'class' => Category::class,
                            'choice_label' => 'titre',
                        ])
                        ->getForm();
        
        $form->handleRequest($request);
        
        

        if($form->isSubmitted()){            
            $fichier = $form->get("image")->getData();
                        
            if($fichier){

                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nomFichier .= uniqid(); # on lui donne un id unique
                $nomFichier .= "." . $fichier->guessExtension();
                $nomFichier = str_replace(" ", "_", $nomFichier);
                //on enregistre le fichier téléchargé dans un dossier public/images
                $dossier = $this->getParameter("dossier_images");
                $fichier->move($dossier, $nomFichier); 
                $article->setImage($nomFichier); 
            }
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Votre article a été modifié !'
            );

            return $this->redirectToRoute("show_article", [
                'titre' => $article->getTitre()
            ]);
        }

        return $this->render('blog/edit.article.html.twig',
        [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null,
            'article' => $article
            
        ]);
    }   
 
    /**
     * @Route("blog/category/new", name="create_category")
     * @Route("blog/category/{slug}/edit", name="edit_category")
     * 
     * @Security("is_granted('ROLE_EDITOR')", message="Cet accès est exclusivement réservé à notre équipe")
     * 
     * @return Response
     */
    public function form_category(Category $category = null, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        if(!$category) {
            $category = new Category;
        }
        $form = $this->createFormBuilder($category)
                        ->add('titre')
                        ->add('slug')
                        ->add('description')
                        ->getForm();
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Votre catégorie a été ajouté !'
            );

            return $this->redirectToRoute("home", [
                'id' => $category->getId()
            ]);
        }

        return $this->render('blog/create.category.html.twig',
        [
            'formCategory' => $form->createView(),
            'editMode' => $category->getId() !== null
        ]);
    }
}
