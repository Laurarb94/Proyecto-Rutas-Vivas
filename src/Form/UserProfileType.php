<?php

namespace App\Form;

use App\Entity\Provinces;
use App\Entity\Localities;
use App\Entity\User;
use App\Repository\ProvincesRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data']; 

        $province = $user->getProvince();

        $builder
            ->add('userName', TextType::class, [ 'label' => 'Nombre de usuario', 'required' => false])
            ->add('name', TextType::class, ['label' => 'Nombre', 'required' => false])
            ->add('firstLastName', TextType::class, ['label' => 'Primer Apellido', 'required' => false])
            ->add('secondLastName', TextType::class, ['label' => 'Segundo Apellido', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Teléfono', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'Correo Electrónico', 'required' => false])
            ->add('biography', TextareaType::class, ['label' => 'Biografía', 'required' => false])
            ->add('photo', FileType::class, [
               'label' => 'Foto',
               'required' => false,
               'mapped' => false,
            ])
            ->add('province', EntityType::class, [
                'class'=> Provinces::class,
                'label' => 'Provincia',
                'required' => false,
                'query_builder' => function (ProvincesRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                    },
            ])
            ->add('locality', EntityType::class, [
                'class'=> Localities::class,
                'choices' => $province ? $province->getLocalities() : [], //si el usua tien eun aprovinciam se cargue directamente la localidad  
                'required' => false,
                'label' => 'Localidad'
            ])
            ->add('addres', TextType::class, ['label' => 'Dirección', 'required' => false])
            ->add('isPrivate', CheckboxType::class, [
                'label' => 'Hacer mi perfil privado',
                'required' => false,
                'mapped' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
