<?php


namespace App\Repository;

use App\Entity\Stadium;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StadiumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stadium::class);
    }

    public function findAllWithClub(): array
{
    return $this->createQueryBuilder('s')
        ->addSelect('c') // Select the associated club entity
        ->leftJoin('s.idclub', 'c') // Join the club entity
        ->getQuery()
        ->getResult();
}


}
