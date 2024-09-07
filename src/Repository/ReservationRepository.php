<?php


namespace App\Repository;

use App\Entity\Club;
use App\Entity\Feedback;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // Repository method to fetch reservation statistics by month
    public function getMonthlyReservationStatistics(): array
    {
        return $this->createQueryBuilder('r')
            ->select("SUBSTRING(r.date, 1, 7) AS monthYear, COUNT(r.id) AS count")
            ->groupBy("monthYear")  
            ->getQuery()
            ->getResult();
    }
    
    public function findByNoFeedback(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.feedbacks', 'f')
            ->where('f.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findReservationsByClubAndUser(Club $club, User $user): array
{
    return $this->createQueryBuilder('r')
        ->join('r.refstadium', 's')
        ->where('s.idclub = :club')
        ->andWhere('r.idplayer = :userId')
        ->setParameter('club', $club)
        ->setParameter('userId', $user->getId())
        ->getQuery()
        ->getResult();
}

public function findByUser(User $idplayer)
{
    return $this->createQueryBuilder('r')
        ->where('r.idPlayer = :idplayer')
        ->setParameter('idplayer', $idplayer)
        ->getQuery()
        ->getResult();
}


    

}
