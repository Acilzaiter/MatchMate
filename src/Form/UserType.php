<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Import FileType
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('phonenumber')
            ->add('birthdate')
            ->add('location')
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Female' => 'female',
                    'Male' => 'male',
                ],
            ])
            ->add('email')
       
           
            // Add a new field for uploading a new image
            ->add('newImage', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
              ->add('creationdate')   // Add the creationdate field
              ->add('status', ChoiceType::class, [
                'choices'  => [
                  'Active' => 'Active',
                  'Inactive' => 'Inactive',
                  'Banned' => 'Banned',
                  'Pending' => 'Pending',
                ],
                'expanded' => false, // Display options as a dropdown (change to true for radio buttons)
              ]);  // Add the status field
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user_image' => null, // Default value for user image
        ]);
    }
}
