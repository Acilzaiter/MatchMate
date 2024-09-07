<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Reservation;
use App\Entity\Stadium;
use App\Entity\User;
use DateTimeImmutable;
use DateTimeInterface;
use App\Form\ReservationType;
use App\Repository\ClubRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ReservationController extends AbstractController
{

    #[Route('/simple/{stadiumId}', name: 'simple_booking')]
    public function index(string $stadiumId): Response
    {
        return $this->render('reservation/simple.html.twig', [
            'stadiumId' => $stadiumId, // Pass the stadiumId to the template
        ]);
    }

    #[Route('/add_reservation/{stadiumId}', name: 'add_reservation')]
    public function add_reservation(string $stadiumId): Response
    {
        return $this->render('reservation/add_reservation.html.twig', [
            'stadiumId' => $stadiumId, // Pass the stadiumId to the template
        ]);
    }

    #[Route('/fetch_time_slots/{stadiumId}/{date}', name: 'fetch_time_slots')]
    public function fetchTimeSlots(string $stadiumId, string $date,EntityManagerInterface $entityManager,ReservationRepository $reservationRepository): Response
    {
        $stadium = $entityManager->getRepository(Stadium::class)->find($stadiumId);

        // // For demonstration purposes, let's generate some dummy time slots
        // $timeSlots = $this->calculateTimeSlots($date,$stadium);
        if (!$stadium) {
            throw $this->createNotFoundException('Stadium not found');
        }

        $club = $entityManager->getRepository(Club::class)->find($stadium->getIdclub()->getId());

        // Retrieve club start and end times
        $clubStartTime = $club->getStarttime();
        $clubEndTime = $club->getEndtime();

        // Calculate time slots based on the stadium's start and end time
        $matchDuration = 90; // Duration of each match in minutes
        $breakTime = 15; // Break time between matches in minutes
        $startTime = $clubStartTime->format('H:i:s');
        $endTime = $clubEndTime->format('H:i:s');
        $timeSlots = [];

        // Convert start and end times to DateTime objects
        $startTime = new DateTime($date . ' ' . $clubStartTime->format('H:i:s'));
        $endTime = new DateTime($date . ' ' . $clubEndTime->format('H:i:s'));

        $interval = new DateInterval('PT' . ($matchDuration + $breakTime) . 'M'); // Interval of match duration plus break time

        // Generate time slots dynamically
        $timeSlots = [];
        while ($startTime < $endTime) {
            $slotEndTime = (clone $startTime)->add(new DateInterval('PT' . $matchDuration . 'M'));
            $timeSlots[] = $startTime->format('h:i A') . ' - ' . $slotEndTime->format('h:i A');
            $startTime->add($interval);
        }
        // Render a template to display the time slots
        return $this->render('reservation/time_slots.html.twig', [
            'timeSlots' => $timeSlots,
            'stadium' => $stadium,
            'endTime' => $startTime,
        ]);
    }

    #[Route('/book/{stadiumId}', name: 'book', methods: ['GET'])]
    public function book(string $stadiumId , EntityManagerInterface $entityManager): Response
    {   
        // Retrieve stadium details
        $stadium = $entityManager->getRepository(Stadium::class)->find($stadiumId);
        
        // Check if the stadium exists
        if (!$stadium) {
            throw $this->createNotFoundException('Stadium not found');
        }

        // Retrieve club start and end times
        $clubStartTime = $stadium->getIdclub()->getStarttime();
        $clubEndTime = $stadium->getIdclub()->getEndtime();

        // Calculate time slots based on the stadium's start and end time
        $matchDuration = 90; // Duration of each match in minutes
        $breakTime = 15; // Break time between matches in minutes
        $startTime = strtotime($clubStartTime->format('H:i:s'));
        $endTime = strtotime($clubEndTime->format('H:i:s'));
        $slots = [];

        // Generate time slots
        while ($startTime < $endTime) {
            $slotEndTime = $startTime + ($matchDuration * 60);
            if ($slotEndTime <= $endTime) {
                $slots[] = [
                    'start' => date('H:i', $startTime),
                    'end' => date('H:i', $slotEndTime),
                ];
            }
            $startTime = $slotEndTime + ($breakTime * 60);
        }

        // Pass the time slots to the template
        return $this->render('reservation/boook.html.twig', [
            'stadiumId' => $stadiumId,
            'slots' => $slots,
        ]);
    }


    #[Route('/slotsavailables/{stadiumId}', name: 'availables', methods: ['GET'])]
    public function availables(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the stadium ID from the request
        $stadiumId = $request->get('stadiumId');

        // Fetch the time slots directly
        $response = $this->forward('App\Controller\ReservationController::book', ['stadiumId' => $stadiumId]);

        // Render the available slots template with the fetched time slots
        return $this->render('reservation/available.html.twig', [
            'slots' => $response->getContent(),
            ]);
    }


    #[Route('/reservation/statistics', name: 'reservation_statistics')]
    public function reservationStatistics(ReservationRepository $reservationRepository): Response
    {
        $reservationStatistics = $reservationRepository->getMonthlyReservationStatistics();
        
        // Render the chart template with the reservation statistics data
        return $this->render('reservation/chart.html.twig', [
            'reservationStatistics' => $reservationStatistics,
        ]);
    }


#[Route('/scheduele/{stadiumId}', name: 'app_reservation_schedule', methods: ['GET'])]
    public function schedule(string $stadiumId, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
    $currentDate = new \DateTime();
    $stadium = $entityManager->getRepository(Stadium::class)->find($stadiumId);
    
    // Check if the stadium exists
    if (!$stadium) {
        throw $this->createNotFoundException('Stadium not found');
    }

    $clubStartTime = $stadium->getIdclub()->getStarttime();
    $clubEndTime = $stadium->getIdclub()->getEndtime();
    // Calculate time slots based on the stadium's start and end time
    $matchDuration = 90; // Duration of each match in minutes
    $breakTime = 15; // Break time between matches in minutes
    $startTime = strtotime($clubStartTime->format('H:i:s'));
    $endTime = strtotime($clubEndTime->format('H:i:s'));
    $slots = [];

    // Generate time slots
    while ($startTime < $endTime) {
        $slotEndTime = $startTime + ($matchDuration * 60);
        $slots[] = [
            'start' => date('H:i', $startTime),
            'end' => date('H:i', $slotEndTime),
        ];
        $startTime = $slotEndTime + ($breakTime * 60);
    }
    $today = new DateTimeImmutable();
    $weekDates = $this->getWeekDates($today);
    
    // Fetch reservations for the specified stadium
    $reservations = $reservationRepository->findBy(['refstadium' => $stadium]);

    // Render the template
    return $this->render('reservation/schedule.html.twig', [
        'current_date' => $currentDate,
        'reservations' => $reservations,
        'weekDates' => $weekDates,
        'slots' => $slots,
        'stadiumId' => $stadiumId,
    ]);
    }

private function getWeekDates(DateTimeInterface $start): array
{
    $weekDates = [];
    $startOfWeek = (new DateTimeImmutable($start->format('Y-m-d')))->setISODate((int)$start->format('Y'), (int)$start->format('W'), 1);
    $weekDates[] = $startOfWeek->format('l d/m/Y');
    for ($i = 1; $i < 7; $i++) {
        $weekDates[] = $startOfWeek->modify("+{$i} day")->format('l d/m/Y');
    }
    return $weekDates;
}


    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(TokenStorageInterface $tokenStorage,Request $request, EntityManagerInterface $entityManager ,ReservationRepository $reservationRepository,ClubRepository $clubRepository): Response
    {

    //$user = $entityManager->getRepository(User::class)->find(1);

    $user = $tokenStorage->getToken()->getUser();
    
    // Fetch the list of clubs
    $clubs = $clubRepository->findAll();

    // Get the selected club ID from the request
    $clubId = $request->query->get('club');

    // Fetch reservations based on the selected club ID
    $reservations = [];
    if ($clubId) {
        // Find stadiums associated with the selected club
        $club = $clubRepository->find($clubId);
        $stadiums = $club->getStadiums();
        
        // Iterate over the stadiums and collect their reservations
        foreach ($stadiums as $stadium) {
            //$reservations = array_merge($reservations, $stadium->getReservations()->toArray());
            $reservations = $reservationRepository->findReservationsByClubAndUser($club, $user);
        }
    } else {
        //$reservations = $reservationRepository->findAll();
        $reservations = $reservationRepository->findBy(['idplayer' => $user]);
    }

    $events = [];
    foreach ($reservations as $reservation) {
        $events[] = [
            'title' => $reservation->getType(), // Use reservation type as event title
            'start' => $reservation->getDate()->format('Y-m-d'), // Start date of reservation
            'end' => $reservation->getDate()->format('Y-m-d'), // End date of reservation (same as start for a single day event)
            'color' => '#3788D8', // Event color (you can customize this)
        ];
    }

    return $this->render('reservation/calendar.html.twig', [
        'events' => json_encode($events), // Pass events to the template
        'clubs' => $clubs,
        'user' => $user
    ]);
    }


    #[Route('/reservation-details', name: 'reservation_details', methods: ['GET'])]
    public function reservationDetails(TokenStorageInterface $tokenStorage,Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {

    //$user = $entityManager->getRepository(User::class)->find(1);
    $user = $tokenStorage->getToken()->getUser();
    $date = new \DateTime($request->query->get('date'));
    //$user = $request->query->get('userId');
    $reservations = $reservationRepository->findByDate($date);
    return $this->render('reservation/reservation_details.html.twig', [
        'reservations' => $reservations,
        'user' => $user
    ]);
    }

    #[Route('/reservation/{stadiumId}', name: 'app_reservation_new', methods: ['POST'])]
    public function new(TokenStorageInterface $tokenStorage,Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, $stadiumId,MailerInterface $mailer): Response
    {
        // Fetching data from POST request
        $selectedDate = $request->request->get('selectedDate');
        $selectedStartTime = $request->request->get('starttime');
        $selectedEndTime = $request->request->get('endtime');

        // Log the received input
        $logger->info('Received selectedDate: '.$selectedDate);
        $logger->info('Received startTime: '.$selectedStartTime);
        $logger->info('Received endTime: '.$selectedEndTime);

        // Convert string dates to DateTime objects and handle potential errors
        try {
            $date = new \DateTimeImmutable($selectedDate);
            $startTime = new \DateTime($selectedStartTime);
            $endTime = new \DateTime($selectedEndTime);
        } catch (\Exception $e) {
            $logger->error('Error parsing date or time: '.$e->getMessage());
            // Redirect to an error page or return an error response
            return $this->redirectToRoute('error_page', ['message' => 'Invalid date or time format']);
        }

        // Check if the time slot is already booked
        $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
            'refstadium' => $stadiumId,  // Make sure 'refstadium' is correctly mapped and used
            'date' => $date,
            'starttime' => $startTime,
            'endtime' => $endTime
        ]);

        if ($existingReservation) {
            $logger->error('Time slot is already booked');
            //$this->addFlash('error', 'This time slot is already booked.');
            return new JsonResponse(['error' => 'This time slot is already booked'], 400); // 400 Bad Request
            //return $this->redirectToRoute('book', ['stadiumId' => $stadiumId]);
        }

        // Retrieve the user (assuming the user is authenticated)
        //$user = $entityManager->getRepository(User::class)->find(1); // Get user with ID 1
        $user = $tokenStorage->getToken()->getUser();
        if (!$user) {
            $logger->error('User not found');
            throw $this->createNotFoundException('User not found');
        }

        $stadium = $entityManager->getRepository(Stadium::class)->find($stadiumId);
        // Check if the stadium exists
        if (!$stadium) {
            $logger->error('Stadium not found');
            throw $this->createNotFoundException('Stadium not found');
        }

        // Create a new reservation instance
        $reservation = new Reservation();
        $reservation->setDate($date);
        $reservation->setStartTime($startTime);
        $reservation->setEndTime($endTime);
        $reservation->setType('Pending');
        $reservation->setRefstadium($stadium);
        $reservation->setIdplayer($user);

        // Persist the reservation entity
        $entityManager->persist($reservation);
        $entityManager->flush();

        $email = (new TemplatedEmail())
        ->from('matchmate59@gmail.com')
        ->to($user->getEmail())
        ->subject('New Reservation Created')
        ->htmlTemplate('emails/emails2.html.twig')
        ->context([
            'reservation' => $reservation,
            'stadium' => $stadium,
            'user' => $user
        ]);

            $mailer->send($email);

        // Redirect to the calendar page or any other page as needed
        return $this->redirectToRoute('calendar');
    }

    #[Route('/reservation/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/{id}/cancel', name: 'cancel_reservation', methods: ['GET'])]
    public function cancelReservation(Reservation $reservation): Response
    {
        $reservation->setType('Canceled');
        $this->getDoctrine()->getManager()->flush();

        // Optionally, add a flash message here

        return $this->redirectToRoute('calendar');
    }

    #[Route('/reservation/{id}/delete', name: 'delete_reservation', methods: ['GET'])]
    public function deleteReservation(Reservation $reservation): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Optional: Check and nullify the reference if allowed
        if ($reservation->getRefStadium()) {
            $reservation->setRefStadium(null);
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        // Optionally, add a flash message here
        $this->addFlash('success', 'Reservation deleted successfully.');

        return $this->redirectToRoute('calendar');
    }




    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }


}
