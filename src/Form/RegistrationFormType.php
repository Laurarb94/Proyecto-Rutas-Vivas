<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Provinces;
use App\Entity\Localities;
use App\Entity\PostalCode;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isClerk = $options['is_clerk'] ?? false;

        if(!$isClerk){
            $builder
            ->add('userName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Nombre de usuario no disponible, por favor elige otro',
                    ]),
                ],
            ])

             ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor introduce un email',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce una contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 4096,
                    ]),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Por favor, debes aceptar nuestros términos.',
                    ]),
                ],
            ]);
        }

        //Campos comunes.

        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce tu nombre',
                    ]),
                ],
            ])
            ->add('firstLastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce tu primer apellido',
                    ]),
                ],
            ])
            ->add('secondLastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce tu segundo apellido',
                    ]),
                ],
            ])
           
            ->add('phone', TelType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce un teléfono',
                    ]),
                    /*
                    new Regex([
                        'pattern' => '(\+34|0034|34)?[ -]*(6|7)[ -]*([0-9][ -]*){8}',  // Expresión regular teléfonos españa
                        'message' => 'Por favor, introduce un número de teléfono válido'
                    ]),*/
                ],
            ])
            
            ->add('photo', FileType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Tamaño máximo de 5MB
                        'mimeTypes' => ['image/jpeg', 'image/png'], // Solo JPG y PNG
                        'mimeTypesMessage' => 'Por favor, sube una imagen en formato JPG o PNG.',
                        'maxSizeMessage' => 'La imagen es demasiado grande. El tamaño máximo permitido es de 5MB.',
                    ])
                ]
            ])
            
            ->add('biography', TextareaType::class, [
                'label' => 'Biografía',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('province', EntityType::class, [
                'class' => Provinces::class,
                'label' => 'Provincia',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off', //evita que el navegador autocomplete automáticamente el campo
                ],
            ])
            ->add('locality', EntityType::class, [
                'class' => Localities::class,
                'label' => 'Localidad',
                'required' => false,
                'attr'=>[
                    'class'=> 'form-control',
                    'autocomplete'=>'off',
                ]
            ])
            /*
            ->add('postalCode', EntityType::class, [
                'class'  => PostalCode::class,
                'label' => 'Código Postal',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' =>'off',
                ],
            ])
            */

            ->add('addres', TextType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            /*
            ->add('photo', FileType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Tamaño máximo de 5MB
                        'mimeTypes' => ['image/jpeg', 'image/png'], // Solo JPG y PNG
                        'mimeTypesMessage' => 'Por favor, sube una imagen en formato JPG o PNG.',
                        'maxSizeMessage' => 'La imagen es demasiado grande. El tamaño máximo permitido es de 5MB.',
                    ])
                ]
            ])*/

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_clerk' => false, 
        ]);
    }
}
