<?php
namespace App\Form;

use App\Entity\Feedback;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reservations = $options['reservations'];

        $builder
            ->add('idReservation', EntityType::class, [
                'class' => Reservation::class,
                'choices' => $reservations,
                'choice_label' => function (Reservation $reservation) {
                    $stadiumReference = $reservation->getRefstadium()->getReference();
                    return sprintf(
                        'Reservation on %s at %s took place in %s',
                        $reservation->getDate()->format('Y-m-d'),
                        $reservation->getStartTime()->format('H:i'),
                        $stadiumReference
                    );
                },
                'choice_value' => 'id',
                'placeholder' => 'Select a reservation',
            ])
            ->add('responseQuestion1', TextareaType::class, [
                'label' => 'What did you like about our service?'
            ])
            ->add('responseQuestion2', TextareaType::class, [
                'label' => 'What can we improve?'
            ])
            ->add('responseQuestion3', TextareaType::class, [
                'label' => 'Any other comments?'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
            'reservations' => [], // default value, can be overridden
        ]);
    }
}
