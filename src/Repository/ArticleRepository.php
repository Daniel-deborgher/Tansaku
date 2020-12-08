<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    // /**
    //  * @return Produit[] Returns an array of Produit objects
    //  */
    
    public function findByWord ($mot) # faire une recherche par un mot, convention : Find
    {
        return $this->createQueryBuilder('a') # récupérer tout les produits
            ->where('a.titre LIKE :val') # récupérer tout les titres avec le mot LIKE = ressemble
            // ->andWhere('p.exampleField = :val') faire andWhere pour d'autres condition
            ->setParameter('val',"%" . $mot . "%")
            ->orderBy('a.titre', 'ASC') # méthode ascendente selon le titre
            ->addOrderBy("a.category") #deuxième paramètre order, null, ASC de base
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    
    public function findByLastCategory($valeur)
    {
        return $this->createQueryBuilder('a')
            ->where('a.category = :val')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(3)
            ->setParameter('val', $valeur)
            ->getQuery()
            ->getResult()
        ;
    } 

        // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    
    public function findByLast($limit)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    } 


    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
