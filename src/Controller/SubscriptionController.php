<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Offer;
use Dompdf\Dompdf;
use App\Entity\User;
use Dompdf\Options;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\NotifierInterface;
use App\Notification\EmailNotification;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    private $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    #[Route('/', name: 'app_subscription_index', methods: ['GET'])]
    public function index(SubscriptionRepository $subscriptionRepository): Response
    {
        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptionRepository->findAll(),
        ]);
    }

    #[Route('/subscription', name: 'app_subscription_new')]
    public function newSubscription(EntityManagerInterface $entityManager): Response
    {
        // Fetch available subscription offers from the database
        $offerRepository = $entityManager->getRepository(Offer::class);
        $offers = $offerRepository->findAll();
    
        return $this->render('subscription/new.html.twig', [
            'offers' => $offers,
        ]);
    }
    #[Route('/subscription/{offerId}', name: 'app_subscription_create')]
public function createSubscription(int $offerId, EntityManagerInterface $entityManager, MailerInterface $mailer, TokenStorageInterface $tokenStorage): RedirectResponse
{
    // Fetch the selected offer from the database
    $offerRepository = $entityManager->getRepository(Offer::class);
    $offer = $offerRepository->find($offerId);

    // Check if the offer exists
    if (!$offer) {
        // Handle error: Offer not found
        // You can redirect the user to an error page or display a flash message
        return $this->redirectToRoute('your_error_route_here');
    }

    // Get the current user (assuming you have implemented user authentication)
  //  $user = $entityManager->getRepository(User::class)->find(2);
    $user = $tokenStorage->getToken()->getUser();



    // Calculate the end date (30 days from now)
    $startDate = new \DateTime();
    $endDate = (clone $startDate)->modify('+30 days');

    // Create a new subscription entity and associate it with the user and the selected offer
    $subscription = new Subscription();
    $subscription->setIduser($user);
    $subscription->setIdoffer($offer);
    $subscription->setStartDate($startDate);
    $subscription->setEndDate($endDate);

    // Save the subscription to the database
    $entityManager->persist($subscription);
    $entityManager->flush();



    // Redirect the user to a success page or another page as needed
    return $this->redirectToRoute('app_club_index');
}
    #[Route('/{id}', name: 'app_subscription_show', methods: ['GET'])]
    public function show(Subscription $subscription): Response
    {
        return $this->render('subscription/show.html.twig', [
            'subscription' => $subscription,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_subscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscription $subscription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('subscription/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->request->get('_token'))) {
            $entityManager->remove($subscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/subscription/{id}/receipt', name: 'subscription_receipt')]
    public function generateReceipt(int $id): Response
    {
        // Fetch the subscription entity based on the provided ID
        $subscription = $this->getDoctrine()->getRepository(Subscription::class)->find($id);
    
        if (!$subscription) {
            throw $this->createNotFoundException('Subscription not found');
        }
    
        // Render the receipt HTML using a Twig template
        $html = $this->renderView('subscription/receipt.html.twig', [
            'subscription' => $subscription,
        ]);
    
        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
    
        // Create a new Dompdf instance
        $dompdf = new Dompdf($options);
    
        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);
    
        // Set paper size and orientation (optional)
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the PDF
        $dompdf->render();
    
        // Get the generated PDF content
        $pdfContent = $dompdf->output();
    
        // Return the PDF as a response
        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="subscription_receipt.pdf"',
        ]);
    }
}
