<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Stadium;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('starttime', TimeType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('endtime', TimeType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('refstadium', EntityType::class, [ // Add the refstadium field
            'class' => Stadium::class, // Use the Stadium entity
            'choice_label' => 'reference', // Display the stadium reference in the dropdown
            'placeholder' => 'Select a stadium', // Placeholder text
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
