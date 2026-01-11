<?php

namespace App\Form;

use App\Entity\Usuario;                        // 👈 Necesario
use Symfony\Component\Form\AbstractType;       // 👈 Necesario
use Symfony\Component\Form\FormBuilderInterface; // 👈 Necesario
use Symfony\Component\Form\Extension\Core\Type\FileType; // 👈 Para el input de archivos
use Symfony\Component\OptionsResolver\OptionsResolver;  // 👈 Para data_class
use Symfony\Component\Validator\Constraints\File;       // 👈 Para validar el archivo

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fotoPerfil', FileType::class, [
                'label' => 'Foto de perfil (jpg, png)',
                'mapped' => false,   // ⚠️ No se guarda directamente en la entidad
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
            'data_class' => Usuario::class,  // ⚠️ Importante para que Symfony sepa que es tu entidad
        ]);
    }
}
