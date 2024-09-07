<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\ClubRepository;
use App\Repository\FeedbackRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'app_feedback_index', methods: ['GET'])]
    public function index(Request $request, FeedbackRepository $feedbackRepository, UserRepository $userRepository): Response
    {
        $playerId = $request->query->get('player');

        $feedbacks = [];

        if ($playerId) {
            // Find feedbacks associated with the selected player
            $player = $userRepository->find($playerId);
            
            if ($player) {
                $feedbacks = $feedbackRepository->findBy(['user' => $player]);
            }
        } else {
            // If no player selected, fetch all feedbacks
            $feedbacks = $feedbackRepository->findAll();
        }

        $players = $userRepository->findByRole('player');

        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
            'players' => $players,
        ]);
    }
    
    #[Route('/new', name: 'app_feedback_new', methods: ['GET', 'POST'])]
    public function new(TokenStorageInterface $tokenStorage, Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $feedback = new Feedback();

        // Get the current logged-in user
        $user = $tokenStorage->getToken()->getUser();

        // Set the current user to the feedback
        $feedback->setUser($user);

        // Fetch reservations for the current user
        $reservations = $reservationRepository->findBy(['idplayer' => $user->getId()]);

        // Create the form with the filtered reservations
        $form = $this->createForm(FeedbackType::class, $feedback, [
            'reservations' => $reservations,
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->redirectToRoute('thanks', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('feedback/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }



    #[Route('/ThankYou', name: 'thanks')]
    public function thankyou(): Response
    {
        return $this->render('feedback/thanks.html.twig', []);
    }

    #[Route('/{id}', name: 'app_feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_feedback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('feedback/edit.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feedback->getId(), $request->request->get('_token'))) {
            $entityManager->remove($feedback);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
    }
}
