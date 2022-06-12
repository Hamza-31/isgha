<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advert>
 *
 * @method Advert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advert[]    findAll()
 * @method Advert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    public function add(Advert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Advert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAdverts(string $query, int $idCategory, array $idLocations){
        $q=$this->createQueryBuilder('ad');
        if($query){
            $q->where(
            $q->expr()->andX(
                $q->expr()->orX(
                    $q->expr()->like('ad.title',':query'),
                    $q->expr()->like('ad.description',':query')
                ),
                $q->expr()->isNotNull('ad.createdAt')
            )
        )
            ->setParameter('query','%'.$query.'%');
        }

        if($idCategory !== 162){
            $q->andWhere('ad.idCategory = :idCategory')
            ->setParameter('idCategory',$idCategory);
        }
        if(!in_array(17,$idLocations)){
            foreach ($idLocations as $idLocation){
                $q->andWhere('ad.idLocation = :idLocation')
                    ->setParameter('idLocation',$idLocation);
            }

        }
        return $q->getQuery()->getResult();

    }

//    /**
//     * @return Advert[] Returns an array of Advert objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Advert
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
