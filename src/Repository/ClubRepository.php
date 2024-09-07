<?php


namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    // You can add custom repository methods here
    public function findAllWithOneStadium()
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.stadiums', 's')
        ->addSelect('s')
        ->groupBy('c.id')
        ->getQuery()
        ->getResult();
}

  // You can add custom repository methods here
  public function findImagesByClubId(int $clubId): array
  {
      $entityManager = $this->getEntityManager();

      $query = $entityManager->createQuery(
          'SELECT i
          FROM App\Entity\Image i
          JOIN i.idclub c
          WHERE c.id = :clubId'
      )->setParameter('clubId', $clubId);

      return $query->getResult();
  }

  // ClubRepository.php
public function searchByName(string $query): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.name LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->getQuery()
        ->getResult();
}



}
