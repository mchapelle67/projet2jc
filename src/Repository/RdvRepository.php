<?php

namespace App\Repository;

use App\Entity\Rdv;
use App\Model\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Rdv>
 */
class RdvRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Rdv::class);
        $this->paginator = $paginator;
    }


    //    /** Resultat de recherche pour les devis
    //     * @param SearchData $searchData
    //     * @return PaginationInterface
    //     */

    public function findBySearch(SearchData $searchData): PaginationInterface
    {
        $rdv = $this->createQueryBuilder('r')
            ->andWhere('r.nom LIKE :q OR r.prenom LIKE :q OR r.email LIKE :q')
            ->setParameter('q', '%' . $searchData->q . '%')
            ->orderBy('r.date_demande', 'DESC');

        $data = $rdv
            ->getQuery()
            ->getResult();

        $rdv = $this->paginator->paginate($data, $searchData->page, 10);

        return $rdv;
    }
    
    //    /**
    //     * @return Rdv[] Returns an array of Rdv objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rdv
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
