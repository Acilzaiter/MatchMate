<?php

namespace App\Form;

use App\Entity\Stadium;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class StadiumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Reference',
                'disabled' => true, // Lock the reference field
            ])
            ->add('height', NumberType::class, [
                'required' => false,
            ])
            ->add('width', NumberType::class, [
                'required' => false,
            ])
            ->add('price', NumberType::class, [
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'label' => 'Images',
                'multiple' => true,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please upload at least one image.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stadium::class,
        ]);
    }
}
