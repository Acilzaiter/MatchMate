<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * MailerService constructor.
     *
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param string $htmlContent
     * @param string $qrCodeFileName
     * @throws TransportExceptionInterface
     */
    public function send(string $subject, string $from, string $to, string $htmlContent, string $qrCodeFileName): void
    {


            $email = (new Email())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->html($htmlContent);


            // Check if QR code file name is provided and attach it to the email
            if ($qrCodeFileName !== '') {
                $email->attachFromPath(dirname(__DIR__, 2) . '/public/EventImages/' . $qrCodeFileName);
            }

            $this->mailer->send($email);

    }
}
