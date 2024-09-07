<?php


namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineExtensions\Query\Mysql\Year;


class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

     // Method to find all users
     public function findAllUsers()
     {
         return $this->createQueryBuilder('u')
             ->getQuery()
             ->getResult();
     }

     // Method to find a user by ID
    public function findById(int $id)
    {
        return $this->find($id);
    }

    // Method to save a user entity
    public function save(User $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // Method to delete a user entity
    public function delete(User $user)
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }
   // Method to find a single user by email criteria
public function findByEmail(String $email)
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.email = :email')
        ->setParameter('email', $email)
        ->getQuery()
        ->getOneOrNullResult();
}

// Method to find users by role criteria
public function findByRole(string $role)
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.role = :role')
        ->setParameter('role', $role)
        ->getQuery()
        ->getResult();
}
 // Method to fetch the counts of users for each role
 public function getRoleCounts(): array
 {
     return $this->createQueryBuilder('u')
         ->select('u.role AS role, COUNT(u.id) AS userCount')
         ->groupBy('u.role')
         ->getQuery()
         ->getResult();
 }

 // Method to count all users
 public function countAllUsers(): int
 {
     return $this->createQueryBuilder('u')
         ->select('COUNT(u.id)')
         ->getQuery()
         ->getSingleScalarResult();
 }

 // Method to count users by gender
 public function countUsersByGender(string $gender): int
 {
     return $this->createQueryBuilder('u')
         ->select('COUNT(u.id)')
         ->andWhere('u.gender = :gender')
         ->setParameter('gender', $gender)
         ->getQuery()
         ->getSingleScalarResult();
 }

 // Method to get age distribution
public function getAgeDistribution(): array
{
    return $this->createQueryBuilder('u')
        ->select('YEAR(CURRENT_DATE()) - YEAR(u.birthdate) AS ageGroup, COUNT(u.id) AS userCount')
        ->groupBy('ageGroup')
        ->orderBy('ageGroup')
        ->getQuery()
        ->getResult();
}


 // Method to count users per role
 public function countUsersPerRole(string $role): int
 {
     return $this->createQueryBuilder('u')
         ->select('COUNT(u.id)')
         ->andWhere('u.role = :role')
         ->setParameter('role', $role)
         ->getQuery()
         ->getSingleScalarResult();
 }



 

 public function findAllOrderedByIdDescQuery($role = null)
 {
     $qb = $this->createQueryBuilder('u')
                ->orderBy('u.id', 'DESC');
 
     if ($role) {
         $qb->where('u.role = :role')
            ->setParameter('role', $role);
     }
 
     return $qb->getQuery();
 }

 /**
     * Get user counts by region.
     *
     * @return array
     */
    public function getUsersByRegion(): array
    {
        // Query to get user counts by region
        $qb = $this->createQueryBuilder('u')
            ->select('u.location, COUNT(u.id) as userCount')
            ->groupBy('u.location');

        $results = $qb->getQuery()->getResult();

        // Reformat the results to make it more suitable for Twig template
        $usersByRegion = [];
        foreach ($results as $result) {
            $usersByRegion[$result['location']] = $result['userCount'];
        }

        return $usersByRegion;
    }

   

}
