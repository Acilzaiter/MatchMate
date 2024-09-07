<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager, string $stripeSK): Response
    {
        $reservationId = $request->request->get('reservationId');
        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);

        if (!$reservation) {
            throw $this->createNotFoundException('No reservation found for id '.$reservationId);
        }

        Stripe::setApiKey($stripeSK);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Reservation for ' . $reservation->getRefstadium()->getIdclub()->getName(),
                        ],
                        'unit_amount' => $reservation->getRefstadium()->getPrice()* 100, // Assuming price is in dollars
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Create a new Payment entity
        $payment = new Payment();
        $payment->setType('card'); // Assuming payment type is 'card'
        $payment->setIduser($reservation->getIdplayer());

        // Associate the payment with the reservation
        $reservation->addIdpayment($payment);
        $reservation->setType("Assigned");

        // Persist the payment and reservation entities
        $entityManager->persist($payment);
        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->redirect($session->url, 303);
    }

    #[Route('/success', name: 'success')]
    public function successUrl(EntityManagerInterface $entityManager): Response
    {
    // Retrieve the payment and related data from the database
    $payment = $entityManager->getRepository(Payment::class)->findOneBy([], ['id' => 'DESC']); // Assuming you're getting the latest payment

    // Retrieve the associated reservation
    $reservations = $payment->getIdreservation();
    $reservation = null;
    foreach ($reservations as $res) {
        // Assuming you want to get the first reservation associated with the payment
        $reservation = $res;
        break;
    }

    if (!$reservation) {
        throw $this->createNotFoundException('No reservation found for the payment.');
    }

    return $this->render('payment/success1.html.twig', [
        'payment' => $payment,
        'reservation' => $reservation,
    ]);
    }


    #[Route('/generate-receipt-pdf', name: 'generate_receipt_pdf', methods: ['POST'])]
    public function generateReceiptPdf(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the payment and reservation IDs from the form submission
        $paymentId = $request->request->get('paymentId');
        $reservationId = $request->request->get('reservationId');

        // Retrieve the payment and reservation entities from the database
        $payment = $entityManager->getRepository(Payment::class)->find($paymentId);
        $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);

        // Render the receipt template with the payment and reservation data
        $html = $this->renderView('payment/receipt.html.twig', [
            'payment' => $payment,
            'reservation' => $reservation,
        ]);

        // Configure Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        // Instantiate Dompdf
        $dompdf = new Dompdf($options);

        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);

        // Render PDF (optional: set paper size, orientation, etc.)
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate PDF file name
        $filename = 'Payment Receipt-' . date('Ymd_His') . '.pdf';

        // Return PDF response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }


    #[Route('/cancel-url', name: 'cancel_url')]
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }
}
