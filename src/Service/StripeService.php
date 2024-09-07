<?php

namespace App\Service;

use App\Entity\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeService
{
    private $privatekey;

    public function __constructor(): void
    {

        $this->privatekey = $_ENV['STRIPE_SECRET_KEY'];
    }


    public function paymentIntent (Event $event)
    {

         \Stripe\Stripe::setApiKey($this->privatekey);
        try {
            return \Stripe\PaymentIntent::create([
                'amount' => $event->getPrice() * 100,
                'currency' => 'tnd',
                'payment_method_types' => ['card'],
            ]);
        } catch (ApiErrorException $e) {
        }

    }


    /**
     * @throws ApiErrorException
     */
    public function paiement ($amount, $currency, $description, array $stripeParameter): ?\Stripe\PaymentIntent
    {

        \Stripe\Stripe::setApiKey($this->privatekey);
        $payment_intent=null;
        if(isset($stripeParameter['stripeIntentId']))
        {

            try {
                $payment_intent = \Stripe\PaymentIntent::retrieve($stripeParameter['stripeIntentId']);
            } catch (ApiErrorException $e) {
            }

            if ($stripeParameter['stripeIntentId']==='succeeded')
            {

            }
            else
                $payment_intent->cancel();



        }
        return $payment_intent;
    }

    /**
     * @throws ApiErrorException
     */
    public function stripe(array $stripeParameter, Event $event): ?\Stripe\PaymentIntent
    {
        return  $this->paiement(
            $event->getPrice()*100,
            'tnd',
            $event->getName(),
            $stripeParameter

        );


    }



}