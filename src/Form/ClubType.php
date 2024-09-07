<?php

// src/Form/ClubType.php

namespace App\Form;

use App\Entity\Club;
use App\Service\CityService; // Import the CityService
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType; // Import TimeType
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use App\Validator\Constraints\AtLeastOneImage;








class ClubType extends AbstractType
{
    private $cityService; // Declare the CityService variable

    public function __construct(cityService $cityService) // Inject the CityService via constructor
    {
        $this->cityService = $cityService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('governorate', ChoiceType::class, [
                'label' => 'Governorate',
                'placeholder' => 'Select Governorate',
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Beja' => 'Beja',
                    'Ben Arous' => 'Ben Arous',
                    'Bizerte' => 'Bizerte',
                    'Gabes' => 'Gabes',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kebili' => 'Kebili',
                    'Kef' => 'Kef',
                    'Mahdia' => 'Mahdia',
                    'Manouba' => 'Manouba',
                    'Medenine' => 'Medenine',
                    'Monastir' => 'Monastir',
                    'Nabeul' => 'Nabeul',
                    'Sfax' => 'Sfax',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Siliana' => 'Siliana',
                    'Sousse' => 'Sousse',
                    'Tataouine' => 'Tataouine',
                    'Tozeur' => 'Tozeur',
                    'Tunis' => 'Tunis',
                    'Zaghouan' => 'Zaghouan',
                ],
                'required' => false, // Make the field required
                'invalid_message' => 'Please select a governorate', // Custom error message if no choice is selected
                'data' => $options['selected_governorate'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a governorate',
                        'allowNull' => true, // Allow null values (if not selected)
                    ]),
                ],
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'City',
                'placeholder' => 'Select Governorate first',
                'choices' => [],
                'required' => false, // Make the field required
                'invalid_message' => 'Please select a city', // Custom error message if no choice is selected
                'data' => $options['selected_city'], // Empty for now
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a city',
                        'allowNull' => true, // Allow null values (if not selected)
                    ]),
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
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Description must be at least {{ limit }} characters long.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Description',
                ],
                'required' => false,

            ])
            ->add('images', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'required' => false, // Make it optional
                'label' => 'Images',
                'constraints' => [
                    // Apply the constraint based on whether it's an edit form or not
                    $options['is_edit_form'] ? new NotBlank() : new AtLeastOneImage(),
                ],
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'required' => false,
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'required' => false,
            ]);
            

        // Add an event listener to update city choices based on selected governorate
        $builder->get('governorate')->addEventListener(
            \Symfony\Component\Form\FormEvents::POST_SUBMIT,
            function (\Symfony\Component\Form\FormEvent $event) {
                $form = $event->getForm();
                $selectedGovernorate = $form->getData();
                $cities = $this->cityService->getCitiesForGovernorate($selectedGovernorate);
                $form->getParent()->add('city', ChoiceType::class, [
                    'label' => 'City',
                    'placeholder' => 'Select City',
                    'choices' => array_combine($cities, $cities),
                ]);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
            'selected_governorate' => null, // Define the default value for selected governorate
            'selected_city' => null, // Define the default value for selected city
            'is_edit_form' => false, // Default value for the flag
            'allow_extra_fields' => true, // Allow extra fields in the form (for updates)

            
        ]);
    }

}
