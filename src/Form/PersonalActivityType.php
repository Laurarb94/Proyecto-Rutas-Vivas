<?php

namespace App\Form;

use App\Entity\CategoryActivity;
use App\Entity\SubcategoryActivity;
use App\Entity\Provinces;
use App\Entity\Localities;
use App\Entity\PersonalActivities;
use App\Entity\User;
use App\Repository\ProvincesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Validator\Constraints\Range;


use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class PersonalActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editing = isset($options['data']) && $options['data']->getId() !== null;

        $builder
            ->add('categoryActivity', EntityType::class, [
                'class' => CategoryActivity::class,
                'choice_label' => 'name',
                'label' => 'Categoría',
                'placeholder' => 'Selecciona una Categoría',
                'mapped' => true,
                'attr' => ['class' => 'category-select'],
            ])

            ->add('subcategoryActivity', EntityType::class, [
                'class' => SubcategoryActivity::class,
                'choice_label' => 'name',
                'choices' => $options['subcategories'] ?? [], 
                'required' => true,
                'label' => 'Subcategoría',
                'placeholder' => 'Selecciona una subcategoría',
                'empty_data' => null, //si no hay nada, poner null para que no de errro
            ])

            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de la Actividad',
            ])

            ->add('name_activity', TextType::class, [
                'label' => 'Nombre de la Actividad',
                'required' => true,
                'attr' => ['class' => 'form-control border-primary'],
            ])

            ->add('imagesActivity', FileType::class, [
                'label' => 'Foto de la actividad',
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*']
            ])

            ->add('companion', EntityType::class, [
                'class' => User::class,
                'mapped' => true,
                'choice_label' => 'userName',
                'choice_value' => 'id',
                'attr' => [
                    'class' =>'form-control',
                    'id' => 'personal_activity_companion',
                    'style' => $editing ? 'display: block' : 'display:none',
                ]
            ])

            ->add('valoration', HiddenType::class, [
                'attr' => ['class' => 'star-rating-input'],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                    ]),
                ]
            ])

            ->add('comment', TextareaType::class, [
                'label' => 'Comentario',
                'required' => false,
            ])
            ->add('province', EntityType::class, [
                'class' => Provinces::class,
                'label' => 'Provincia',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off', //evita que el navegador autocomplete automáticamente el campo
                ],
                'query_builder' => function (ProvincesRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                    },
            ])
            ->add('locality', EntityType::class, [
                'class' => Localities::class,
                'label' => 'Localidad',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ]
            ])
        ;

        // Verificar si estás editando la actividad
        if($editing && count($options['data']->getImagesActivity()) > 0){
            //campo visible cuando estás editando y hay una foto
            $builder->add('delete_image', CheckboxType::class, [
                'label'=>'¿Borrar la foto?',
                'required'=>false,
                'mapped'=>false,
            ]);
        }else{
            //campo oculto cuando no estás editando, cuando estás creando: 
            $builder->add('delete_image', HiddenType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['style' => 'display:none;'],
            ]);
        }

        //El formModifier te permite modificar el formulario de manera dinámica: Dinamismo para las subcategorías
        $formModifier = function (FormInterface $form, ?CategoryActivity $category = null, ?SubcategoryActivity $selectedSubcategory = null) {
            $subcategories = $category ? $category->getSubcategories() : [];
            
            // Añadir la subcategoría seleccionada si no está en la lista
            if ($selectedSubcategory && !in_array($selectedSubcategory, $subcategories->toArray(), true)) {
                $subcategories[] = $selectedSubcategory;
            }
            
            $form->add('subcategoryActivity', EntityType::class, [
                'class' => SubcategoryActivity::class,
                'choices' => $subcategories,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Subcategoría',
                'data' => $selectedSubcategory, // esto asegura que se preseleccione
                ]);
            };


        // Cuando se carga el formulario por primera vez
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formModifier(
                $event->getForm(),
                $data->getCategoryActivity(),
                $data->getSubcategoryActivity()
            );
        });


        // Cuando el usuario cambia la categoría, se actualizan las subcat dinámicamente
        $builder->get('categoryActivity')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $category = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $category);
            }
        );
    }


        

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersonalActivities::class,
        ]);

        $resolver->setDefined(['categories', 'companion', 'subcategories']);
    }
}
