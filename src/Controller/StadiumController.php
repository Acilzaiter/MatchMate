<?php

namespace App\Controller;

use App\Entity\Stadium;
use App\Form\StadiumType;
use App\Repository\StadiumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Club;
use App\Repository\ClubRepository;
use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;




#[Route('/stadium')]
class StadiumController extends AbstractController
{
    private $emailService;


    #[Route('/', name: 'app_stadium_index', methods: ['GET'])]
    public function index(StadiumRepository $stadiumRepository, FlashyNotifier $flashy): Response
    {
        $stadia = $stadiumRepository->findAll();
    
        // Check if any stadium has maintenance equal to 3
        $maintenanceAlert = false;
        foreach ($stadia as $stadium) {
            if ($stadium->getMaintenance() == 3) {
                $maintenanceAlert = true;
                break;
            }
        }
        $flashy->success('A Club got created 1 second ago', 'http://your-awesome-link.com');

        // Show flash notifier if maintenance alert is true
        if ($maintenanceAlert) {
            $flashy->success('A Club got created 1 second ago', 'http://your-awesome-link.com');
        }
    
        return $this->render('stadium/index.html.twig', [
            'stadia' => $stadia,
        ]);
    }
    
    #[Route('/{id}/stadiums', name: 'app_club_stadiums', methods: ['GET'])]
public function showStadiums(Club $club, StadiumRepository $stadiumRepository): Response
{
    // Retrieve stadiums associated with the club
    $stadiums = $stadiumRepository->findBy(['idclub' => $club]);

    return $this->render('stadium/stadiums.html.twig', [
        'club' => $club,
        'stadiums' => $stadiums,
    ]);
}

#[Route('/new/{clubId}', name: 'app_stadium_new', methods: ['GET', 'POST'])]
public function new(TokenStorageInterface $tokenStorage,Request $request, EntityManagerInterface $entityManager, ClubRepository $clubRepository, $clubId,MailerInterface $mailer): Response
{
     // Fetch existing images (only if editing an existing club)
     $existingImages = [];

     $user = $tokenStorage->getToken()->getUser();

    $stadium = new Stadium();
    // Set rate and maintenance to 0
    $stadium->setRate(0);
    $stadium->setMaintenance(0);

    // Get the specific club from the database
    $club = $clubRepository->find($clubId);

        // Increment stadiumnbr for the club
        $stadiumNbr = $club->getStadiumnbr();
        $club->setStadiumnbr($stadiumNbr + 1);

    // Set the idclub foreign key
    $stadium->setIdclub($club);

    // Generate reference based on club and random number
    $reference = strtoupper(substr($club->getName(), 0, 3)) . '' . mt_rand(0, 999);
    $stadium->setReference($reference);

    $form = $this->createForm(StadiumType::class, $stadium);
    $form->handleRequest($request);

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
            $image->setType('stadium');

            $entityManager->persist($image);
            $stadium->addIdimage($image);
        }
        
        // Handle image deletion
        $removedImages = $request->request->get('removed_images');
        if (is_iterable($removedImages) && !empty($removedImages)) {
            foreach ($removedImages as $removedImageId) {
                // Find the image entity
                $image = $entityManager->getRepository(Image::class)->find($removedImageId);

                if ($image) {
                    // Remove the association between the club and the image
                    $stadium->removeIdimage($image);
                    // Remove the image entity from the database
                    $entityManager->remove($image);
                }
            }
        }

        $entityManager->persist($stadium);
        $entityManager->flush();

        $email = (new TemplatedEmail())
            ->from('matchmate59@gmail.com')
            ->to($user->getEmail())
            ->subject('New Stadium Created')
            ->htmlTemplate('emails/emailss.html.twig')
            ->context([
                'stadium' => $stadium,
                'user' => $user
            ]);

            $mailer->send($email);


        return $this->redirectToRoute('app_club_stadiums', ['id' => $clubId], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('stadium/new.html.twig', [
        'club' => $club, // Pass the club variable to the template
        'stadium' => $stadium,
        'form' => $form,
        'existingImages' => $existingImages, // Pass an empty array for new clubs

    ]);
}


    #[Route('/{reference}', name: 'app_stadium_show', methods: ['GET'])]
    public function show(Stadium $stadium): Response
    {
        return $this->render('stadium/show.html.twig', [
            'stadium' => $stadium,
        ]);
    }

    #[Route('/{reference}/edit', name: 'app_stadium_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Stadium $stadium, EntityManagerInterface $entityManager): Response
{
      // Fetch existing images associated with the stadium
      $existingImages = $stadium->getIdimage()->toArray();

    $form = $this->createForm(StadiumType::class, $stadium);
    $form->handleRequest($request);

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
            $image->setType('stadium');

            $entityManager->persist($image);
            $stadium->addIdimage($image);
        }
        
        // Handle image deletion
        $removedImages = $request->request->get('removed_images');
        if (is_iterable($removedImages) && !empty($removedImages)) {
            foreach ($removedImages as $removedImageId) {
                // Find the image entity
                $image = $entityManager->getRepository(Image::class)->find($removedImageId);

                if ($image) {
                    // Remove the association between the club and the image
                    $stadium->removeIdimage($image);
                    // Remove the image entity from the database
                    $entityManager->remove($image);
                }
            }
        }


        $entityManager->flush();

        // Redirect to the club's stadiums page after editing the stadium
        return $this->redirectToRoute('app_club_stadiums', ['id' => $stadium->getIdclub()->getId()], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('stadium/edit.html.twig', [
        'stadium' => $stadium,
        'form' => $form,
        'existingImages' => $existingImages,

    ]);
}


    #[Route('/{reference}', name: 'app_stadium_delete', methods: ['POST'])]
    public function delete(Request $request, Stadium $stadium, EntityManagerInterface $entityManager): Response
    {
        $club = $stadium->getIdclub(); // Retrieve the associated club
    
        if ($this->isCsrfTokenValid('delete'.$stadium->getReference(), $request->request->get('_token'))) {
            // Decrement the stadiumnbr attribute of the associated club
            $stadiumNbr = $club->getStadiumnbr();
            $club->setStadiumnbr($stadiumNbr - 1);
    
            // Remove the stadium
            $entityManager->remove($stadium);
            $entityManager->flush();
        }
    
        // Redirect to club stadiums
        return $this->redirectToRoute('app_club_stadiums', ['id' => $club->getId()], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/stadium/{reference}/alert-maintenance', name: 'alert_maintenance', methods: ['POST'])]
    public function alertMaintenance(Request $request, Stadium $stadium)
    {
        // Check if the stadium's maintenance status is 3 and it's not verified
        if ($stadium->getMaintenance() == 3) {
            // Update the maintenance status to 0
            $stadium->setMaintenance(0);

            // Persist changes to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($stadium);
            $entityManager->flush();

            // Return a success JSON response
            return new JsonResponse(['message' => 'Maintenance alerted successfully.'], JsonResponse::HTTP_OK);
        }

        // Return a failure JSON response
        return new JsonResponse(['message' => 'Unable to alert maintenance.'], JsonResponse::HTTP_BAD_REQUEST);
    }

    
}
