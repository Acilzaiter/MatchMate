<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\User;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MercurySeries\FlashyBundle\FlashyNotifier;
use App\Entity\Image;
use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\CityService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;
use App\Entity\Notification; // Don't forget to import the Notification entity
use App\Repository\NotificationRepository;
use DateInterval;
use App\Notification\EmailNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class ClubController extends AbstractController
{
    #[Route('/test', name: 'app_club_index1', methods: ['GET'])]
    public function index1(ClubRepository $clubRepository, FlashyNotifier $flashy, MailerInterface $mailer): Response
    {

        $googleMapsApiKey = getenv('GOOGLE_MAPS_API_KEY');

       
     //   $flashy->success('Event created!', 'http://your-awesome-link.com');
       $email = (new Email())
        ->from('symfonymail022@gmail.com')
        ->to('elyesnart@gmail.com')
        ->subject('Time for symfony Mailer')
        ->text('Sending emails is fun again')
        ->html('<p> see </p>');

        $mailer->send($email);

        return $this->render('home/test.html.twig', [
            'googleMapsApiKey' => $googleMapsApiKey,
        ]);
    }

    #[Route('/club', name: 'app_club_index', methods: ['GET'])]
    public function index(TokenStorageInterface $tokenStorage,ClubRepository $clubRepository, EntityManagerInterface $entityManager, FlashyNotifier $flashy, SubscriptionRepository $subscriptionRepository, NotificationRepository $notificationRepository): Response
    {
        $user = $tokenStorage->getToken()->getUser();

        if($user->getRoles()[0] == 'Admin'){
            $clubs = $clubRepository->findAll();
        } else {
            $clubs = $clubRepository->findBy(['iduser' => $user]);
        }

        $hasSubscription = $subscriptionRepository->findOneBy(['iduser' => $user]) !== null;
        $subscription = $subscriptionRepository->findOneBy(['iduser' => $user]);
        $notifications = $notificationRepository->findBy(['iduser' => $user]);
        
        $maxClubsReached = false;
        $subscriptionStartDate = null;
        $subscriptionEndDate = null;
        if ($hasSubscription) {
            $userSubscription = $subscriptionRepository->findOneBy(['iduser' => $user]);
            $maxClubs = $userSubscription->getIdoffer()->getNbrclub(); // Assuming this gets the maximum number of clubs allowed in the subscription offer
            $numberOfClubs = count($user->getClubs()); // Assuming this gets the number of clubs the user already has
            $maxClubsReached = $numberOfClubs >= $maxClubs;
               // Get the subscription start and end dates
        $subscriptionStartDate = $userSubscription->getStartDate();
        $subscriptionEndDate = $userSubscription->getEndDate();
        }

        $hasFreeClub = count($user->getClubs()) > 0;

        // Extract unique governorates and cities from clubs
        $governorates = [];
        $cities = [];
        foreach ($clubs as $club) {
            if (!in_array($club->getGovernorate(), $governorates)) {
                $governorates[] = $club->getGovernorate();
            }
            if (!in_array($club->getCity(), $cities)) {
                $cities[] = $club->getCity();
            }
        }



        return $this->render('club/index.html.twig', [
            'clubs' => $clubs,
            'governorates' => $governorates,
            'cities' => $cities,
            'hasSubscription' => $hasSubscription, // Pass subscription status to the Twig template
            'maxClubsReached' => $maxClubsReached, // Pass maxClubsReached to the Twig template
            'hasFreeClub' =>  $hasFreeClub,
            'subscriptionStartDate' => $subscriptionStartDate, // Pass subscription start date to the Twig template
        'subscriptionEndDate' => $subscriptionEndDate, // Pass subscription end date to the Twig template
        'subscription' => $subscription, // Pass the subscription variable to the Twig template
        'notifications' => $notifications, // Pass notifications to the Twig template

        ]);
    }

    #[Route('/club/new', name: 'app_club_new', methods: ['GET', 'POST'])]
    public function new(TokenStorageInterface $tokenStorage,Request $request, EntityManagerInterface $entityManager, FlashyNotifier $flashy, MailerInterface $mailer): Response
    {

           // Set a flag to indicate new club creation

        $club = new Club();
        
        $user = $tokenStorage->getToken()->getUser();
    
        // Set the user for the club
        $club->setIduser($user);
    
        // Set the start time to the current time
        $club->setStarttime(new DateTime());
    
        // Set the end time to 24 hours later
        $endTime = new DateTime();
        $endTime->add(new DateInterval('PT24H'));
        $club->setEndtime($endTime);
    
        // Set stadiumnbr, longitude, and latitude to 0
        $club->setStadiumnbr(0);
       // $club->setLongitude(0);
        // $club->setLatitude(0);

        // Create and handle the form
        $form = $this->createForm(ClubType::class, $club, [
            'is_edit_form' => true, // Pass the flag to the form builder

        ]);
        $form->handleRequest($request);
    
        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form['images']->getData();

            foreach ($images as $imageFile) {
                // Your image upload logic here
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/Images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload the image.');
                }

                $image = new Image();
                $image->setName($originalFilename);
                $image->setUrl('Images/' . $newFilename);
                $image->setType('club');

                $entityManager->persist($image);
                $club->addIdimage($image);
            }
            $removedImages = $request->request->get('removed_images');
            if (is_iterable($removedImages) && !empty($removedImages)) {
                foreach ($removedImages as $removedImageId) {
                    // Find the image entity
                    $image = $entityManager->getRepository(Image::class)->find($removedImageId);
    
                    if ($image) {
                        // Remove the association between the club and the image
                        $club->removeIdimage($image);
                        // Remove the image entity from the database
                        $entityManager->remove($image);
                    }
                }
            }

            $entityManager->persist($club);
            $entityManager->flush();

             // Create a DateTime object for the current time
            $currentDateTime = new DateTime();

            // Format the current time with minutes, seconds, and hours
            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

            // Create the notification text with the formatted date and club name
            $notificationText = 'New club created: ' . $club->getName() . ' on ' . $formattedDateTime;

            // Create a new notification
            $notification = new Notification();
            $notification->setText($notificationText);
            $notification->setIduser($user);
            // Persist and flush the notification entity
            $entityManager->persist($notification);
            $entityManager->flush();
            $flashy->success('A Club got created 1 second ago', 'http://your-awesome-link.com');

            $email = (new TemplatedEmail())
            ->from('matchmate59@gmail.com')
            ->to($user->getEmail())
            ->subject('New Club Created')
            ->htmlTemplate('emails/emails.html.twig')
            ->context([
                'club' => $club,
                'user' => $user
            ]);

            $mailer->send($email);


            return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
            $flashy->success('Event created!', 'http://your-awesome-link.com');


        }
    
        // Render the new club form
        return $this->renderForm('club/new.html.twig', [
            'club' => $club,
            'form' => $form,
        ]);
    }
    #[Route('/club/{id}/edit', name: 'app_club_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Club $club, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
           // Get the selected governorate and city from the club
    $selectedGovernorate = $club->getGovernorate();
    $selectedCity = $club->getCity();

    // Create the edit form and pass the selected governorate and city values
    $form = $this->createForm(ClubType::class, $club, [
        'selected_governorate' => $selectedGovernorate,
        'selected_city' => $selectedCity,
        'is_edit_form' => false, // Pass the flag to the form builder

    ]);
    
        $form->handleRequest($request);

        dump($form);
        dump($selectedCity);
        dump($selectedGovernorate);
        
    
        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form['images']->getData();
            foreach ($images as $imageFile) {
                // Your image upload logic here
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/Images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload the image.');
                }

                $image = new Image();
                $image->setName($originalFilename);
                $image->setUrl('Images/' . $newFilename);
                $image->setType('club');

                $entityManager->persist($image);
                $club->addIdimage($image);
            }
            
            // Handle image deletion
            $removedImages = $request->request->get('removed_images');
            if (is_iterable($removedImages) && !empty($removedImages)) {
                foreach ($removedImages as $removedImageId) {
                    // Find the image entity
                    $image = $entityManager->getRepository(Image::class)->find($removedImageId);
    
                    if ($image) {
                        // Remove the association between the club and the image
                        $club->removeIdimage($image);
                        // Remove the image entity from the database
                        $entityManager->remove($image);
                    }
                }
            }

            

            $entityManager->flush();
            return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('club/edit.html.twig', [
            'club' => $club,
            'form' => $form,
        ]);
    }
    

    
    
#[Route('/club/{id}/delete', name: 'app_club_delete', methods: ['POST'])]
public function delete(TokenStorageInterface $tokenStorage,Request $request, Club $club, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete' . $club->getId(), $request->request->get('_token'))) {
        // Remove images associated with the club
        foreach ($club->getIdimage() as $image) {
            $entityManager->remove($image);
        }
      //  $user = $entityManager->getRepository(User::class)->find(2);
        $user = $tokenStorage->getToken()->getUser();


        // Create a DateTime object for the current time
$currentDateTime = new DateTime();

// Format the current time with minutes, seconds, and hours
$formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

// Create the notification text with the formatted date and club name
$notificationText = 'Club: ' . $club->getName() . ' Got Deleted on ' . $formattedDateTime;

// Create a new notification
$notification = new Notification();
$notification->setText($notificationText);
$notification->setIduser($user);
// Persist and flush the notification entity
$entityManager->persist($notification);
$entityManager->flush();

        // Remove the club itself
        $entityManager->remove($club);
        $entityManager->flush();
    }

    // Redirect to index page after deletion
    return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
}

    

#[Route('/club/{id}/show', name: 'app_club_show', methods: ['GET'])]
public function show(Club $club): Response
{
    return $this->render('club/show.html.twig', [
        'club' => $club,
    ]);
}


#[Route('/club/fetch-cities', name: 'fetch_cities', methods: ['POST'])]
public function fetchCities(Request $request, CityService $cityService): JsonResponse
{
    // Get the selected governorate from the request
    $governorate = $request->request->get('governorate');

    // Fetch cities for the selected governorate using the CityService
    $cities = $cityService->getCitiesForGovernorate($governorate);

    return $this->json(['cities' => $cities]);
}


}
