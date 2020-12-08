<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{
    /**
     * @Route("/recherche", name="recherche")
     */
    public function index(ArticleRepository $ar, Request $rq)
    {
        $globaleGet = $rq->query; # contient les valeurs de $_GET
        $mot = $globaleGet->get("recherche"); # recherche est le name de mon formulaire
        $articles = $ar->findByWord($mot);
        return $this->render('search/index.html.twig',compact("articles", "mot"));
    }
}