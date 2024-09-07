<?php

namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($claim, $email): void
    {
        // Composer le contenu de l'e-mail avec les détails de la recette
        $message = (new Email())
            ->from('saropez.pro@gmail.com')
            ->to($email)
            ->subject('Confirmation de reclamation ')
            ->text('Your claim has been sent and saved successfully.')
            ->html('<p>Description: ' . $claim->getDescription() . '</p>' . '<p>Will look to your claim and treat it carfully in shortest notice ; thanks for anderstanding.</p>');
            
        $this->mailer->send($message);
    }
}