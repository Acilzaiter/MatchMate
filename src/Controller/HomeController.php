<?php

namespace App\Controller;

use App\Entity\Club;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ClubRepository;
use App\Repository\StadiumRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Stadium;
use App\Entity\Liked;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class HomeController extends AbstractController
{
    private $client;



    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/home1', name: 'app_home1')]
    public function index(): Response
    {
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/home', name: 'app_home')]
    public function index2(ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findAllWithOneStadium();
        $clubImages = [];

        foreach ($clubs as $club) {
            $images = $clubRepository->findImagesByClubId($club->getId());
            $clubImages[$club->getId()] = $images;
        }
        $clubs1 = $clubRepository->findAll();

        $governorates = [];
        $cities = [];
        foreach ($clubs1 as $club) {
            if (!in_array($club->getGovernorate(), $governorates)) {
                $governorates[] = $club->getGovernorate();
            }
            if (!in_array($club->getCity(), $cities)) {
                $cities[] = $club->getCity();
            }
        }

        return $this->render('home/home1.html.twig', [
            'controller_name' => 'HomeController',
            'clubs' => $clubs,
            'governorates' => $governorates,
            'cities' => $cities,
            'clubImages' => $clubImages,
        ]);
    }


    #[Route('/stade/{id}', name: 'app_stade')]
    public function stade($id, TokenStorageInterface $tokenStorage, StadiumRepository $stadiumRepository, EntityManagerInterface $entityManager): Response
    {
        // Fetch the club using the provided ID
        $club = $entityManager->getRepository(Club::class)->find($id);

        // Check if the club exists
        if (!$club) {
            throw $this->createNotFoundException('Club not found');
        }

        // Fetch stadiums associated with the club
        $stadiums = $club->getStadiums();

        // Fetch the current user
        $user = $tokenStorage->getToken()->getUser();

        return $this->render('home/stade.html.twig', [
            'stadiums' => $stadiums,
            'currentUser' => $user,
        ]);
    }

    #[Route('/toggle-like/{id}', name: 'toggle_like')]
    public function toggleLike(TokenStorageInterface $tokenStorage,Stadium $stadium, Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $currentUser = $tokenStorage->getToken()->getUser();

        if (!$currentUser) {
            return new JsonResponse(['error' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Check if the user has already liked the stadium
        if ($stadium->getIduser()->contains($currentUser)) {
            // User has already liked the stadium, remove like
            $stadium->removeIduser($currentUser);
            $stadium->setRate($stadium->getRate() - 1); // Decrease rate when unliked
        } else {
            // User hasn't liked the stadium, check if they have disliked it before
            if ($stadium->getIduser()->isEmpty()) {
                // User hasn't interacted with the stadium before, like it
                $stadium->addIduser($currentUser);
                $stadium->setRate($stadium->getRate() + 1); // Increase rate when liked
            } else {
                // User has interacted with the stadium before, so they are changing from dislike to like
                $stadium->addIduser($currentUser);
                $stadium->setRate($stadium->getRate() + 2); // Increase rate by 2 when changing from dislike to like
            }
        }

        $entityManager->flush();

        return new JsonResponse(['liked' => $stadium->getIduser()->contains($currentUser)]);
    }



    #[Route('/like/{id}', name: 'like_stadium')]
    public function likeStadium(TokenStorageInterface $tokenStorage,Stadium $stadium, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $tokenStorage->getToken()->getUser();

        if (!$currentUser) {
            return new Response('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user has already liked the stadium
        $liked = $entityManager->getRepository(Liked::class)->findOneBy(['user' => $currentUser, 'stadium' => $stadium]);

        if ($liked) {
            // User has already liked the stadium, do nothing
            return new Response('Stadium already liked', Response::HTTP_OK);
        }

        // Increment the rate of the stadium
        $stadium->setRate($stadium->getRate() + 1);

        // Like the stadium
        $liked = new Liked();
        $liked->setUser($currentUser);
        $liked->setStadium($stadium);

        $entityManager->persist($liked);
        $entityManager->flush();

        return new Response('Stadium liked', Response::HTTP_OK);

    }

    #[Route('/dislike/{id}', name: 'dislike_stadium')]
    public function dislikeStadium(TokenStorageInterface $tokenStorage,Stadium $stadium, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $tokenStorage->getToken()->getUser();

        if (!$currentUser) {
            return new Response('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        // Find the liked record
        $liked = $entityManager->getRepository(Liked::class)->findOneBy(['user' => $currentUser, 'stadium' => $stadium]);

        if (!$liked) {
            // User hasn't liked the stadium, do nothing
            return new Response('Stadium not liked', Response::HTTP_OK);
        }

        // Decrement the rate of the stadium
        $stadium->setRate($stadium->getRate() - 1);

        // Remove the liked record
        $entityManager->remove($liked);
        $entityManager->flush();

        return new Response('Stadium disliked', Response::HTTP_OK);

    }
    #[Route('/stadium-status/{id}', name: 'stadium_status')]
    public function getStadiumStatus(Stadium $stadium, EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch the current user (replace 2 with the actual user ID)
        $currentUser = $entityManager->getRepository(User::class)->find(2);

        if (!$currentUser) {
            return new JsonResponse(['liked' => false]); // User not authenticated, stadium is not liked
        }

        // Check if the stadium is liked by the user
        $liked = $entityManager->getRepository(Liked::class)->findOneBy(['user' => $currentUser, 'stadium' => $stadium]);

        return new JsonResponse(['liked' => $liked !== null]); // Return whether the stadium is liked
    }
    
    #[Route('/weather', name: 'weather_route_name')]
    public function showWeather(): Response
    {
        $apiKey = 'c55da2255b6d361f889fbaf7c3d4e804';
        $url = 'https://api.openweathermap.org/data/2.5/weather?q=Tunis&appid=' . $apiKey . '&units=metric';

        $response = $this->client->request('GET', $url);
        $weatherData = $response->toArray();

        return $this->render('home/weather.html.twig', [
            'weatherData' => $weatherData
        ]);
    }

}
