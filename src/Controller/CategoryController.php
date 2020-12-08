<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CategoryController extends AbstractController
{
    /**
     * Permet de voir les articles dans la categorie manga
     * 
     * @Route("/tansaku/rubrique/manga", name="show_category_manga")
     * @return Response
     */
    public function show_category_manga(CategoryRepository $repo)
    {
        
        $category = $repo->findOneByTitre('Manga');

        return $this->render('category/view.html.twig', [
            'category' => $category
        ]);
    }    

    /**
     * Permet de voir les articles dans la categorie animé
     * 
     * @Route("/tansaku/rubrique/anime", name="show_category_anime")
     * @return Response
     */
     public function show_category_anime(CategoryRepository $repo)
     {
         
         $category = $repo->findOneByTitre('Anime');
 
         return $this->render('category/view.html.twig', [
             'category' => $category
         ]);
     }    
     
     /**
     * Permet de voir les articles dans la categorie jeux vidéo
     * 
     * @Route("/tansaku/rubrique/jeux-video", name="show_category_jeux_video")
     * @return Response
     */
     public function show_category_jeux_video(CategoryRepository $repo)
     {
         
         $category = $repo->findOneByTitre('Jeux Video');
 
         return $this->render('category/view.html.twig', [
             'category' => $category
         ]);
     }

    /**
     * Permet de voir les articles dans la categorie à découvrir
     * 
     * @Route("tansaku/rubrique/a_decouvrir", name="show_category_a_decouvrir")
     * @return Response
     */
    public function show_category_a_decouvrir(CategoryRepository $repo)
    {
        
        $category = $repo->findOneByTitre('A découvrir');

        return $this->render('category/view.html.twig', [
            'category' => $category
        ]);
    }    

        /**
     * @Route("tansaku/nouvelle-rubrique", name="create_category")
     * @Route("tansaku/rubrique/{slug}/edit", name="edit_category")
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
