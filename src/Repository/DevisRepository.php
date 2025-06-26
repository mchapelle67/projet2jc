<?php

namespace App\Repository;

use App\Entity\Devis;
use App\Model\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Devis>
 */

class DevisRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Devis::class);
        $this->paginator = $paginator;
    }


    //    /** Resultat de recherche pour les devis
    //     * @param SearchData $searchData
    //     * @return PaginationInterface
    //     */

    public function findBySearch(SearchData $searchData): PaginationInterface
    {
        $devis = $this->createQueryBuilder('d')
            ->andWhere('d.nom LIKE :q OR d.prenom LIKE :q OR d.email LIKE :q OR d.id LIKE :q')
            ->setParameter('q', '%' . $searchData->q . '%')
            ->orderBy('d.date_devis', 'DESC');

        $data = $devis
            ->getQuery()
            ->getResult();

        $devis = $this->paginator->paginate($data, $searchData->page, 10);

        return $devis;
    }

    //    /**
    //     * @return Devis[] Returns an array of Devis objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Devis
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

}
