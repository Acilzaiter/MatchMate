<?php


namespace App\Controller;
use App\Controller\UserController;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;


use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\ORM\QueryBuilder;







class AdminController extends AbstractController
{   

  
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Get total number of users
        $totalUsers = $userRepository->countAllUsers();

        // Get the number of users per role
            $adminCount = $userRepository->countUsersPerRole('Admin');
            $fieldOwnerCount = $userRepository->countUsersPerRole('FieldOwner');
            $playerCount = $userRepository->countUsersPerRole('Player');

        // Get count of females and males
        $femaleCount = $userRepository->countUsersByGender('female');
        $maleCount = $userRepository->countUsersByGender('male');

         // Get age repartition
        $ageDistribution = $userRepository->getAgeDistribution();
        // Format the data for use in the chart
        $userAges = [];
        foreach ($ageDistribution as $data) {
            $userAges[] = ['ageGroup' => $data['ageGroup'], 'userCount' => $data['userCount']];
        }


        // Retrieve user data with their regions using Doctrine ORM
        $usersByRegion =  $userRepository->getUsersByRegion();


        return $this->render('dashboard_admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'totalUsers' => $totalUsers,
            'adminCount' => $adminCount,
            'fieldOwnerCount' => $fieldOwnerCount,
            'playerCount' => $playerCount,
            'femaleCount' => $femaleCount,
            'maleCount' => $maleCount,
            'userAges' => $userAges,
            'usersByRegion' => $usersByRegion,
        ]);
    }

    #[Route('/dashboard/users/{page}', name: 'dashUsers', requirements: ['page' => '\d+'])]
public function dashUsers(Request $request, UserRepository $userRepository): Response
{
    $itemsPerPage = 5; // Define the number of items per page
    $currentPage = $request->query->getInt('page', 1); // Retrieve the current page number from the query parameters
    $role = $request->query->get('role'); // Retrieve the role from the query parameters

    $query = $userRepository->findAllOrderedByIdDescQuery($role);

    // Initialize the Doctrine Paginator with the query
    $paginator = new Paginator($query);

    // Set the pagination parameters
    $paginator->getQuery()
              ->setFirstResult($itemsPerPage * ($currentPage - 1))
              ->setMaxResults($itemsPerPage);

    // Calculate the total number of items and pages
    $totalItems = count($paginator);
    $pagesCount = ceil($totalItems / $itemsPerPage);

    // Render the template with the paginated results
    return $this->render('Dashbord_users/dashboard.html.twig', [
        'users' => $paginator,
        'CurrentPage' => $currentPage,
        'pagesCount' => $pagesCount,
        'selectedRole' => $role, // Pass the selected role to the template
    ]);
}


    
}
