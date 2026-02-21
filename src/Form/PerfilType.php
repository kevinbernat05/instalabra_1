<?php

namespace App\Form;

use App\Entity\Usuario;                       
use Symfony\Component\Form\AbstractType;       
use Symfony\Component\Form\FormBuilderInterface; 
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\OptionsResolver\OptionsResolver;  
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nombre', TextType::class, [
            'label' => false,
            'required' => true,
        ])
        ->add('biografia', TextareaType::class, [
            'label' => false,
            'required' => false,
            'attr' => ['rows' => 3],
        ])
            ->add('fotoPerfil', FileType::class, [
                'label' => 'Foto de perfil (jpg, png)',
                'mapped' => false,  
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Sube un archivo válido (jpg o png)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class, 
        ]);
    }
}
