<?php

namespace App\Form;

use App\Entity\Palabra;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PalabraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('palabra', null, [
                'label' => false,
                'attr' => ['placeholder' => 'Palabra', 'class' => 'form-control', 'style' => 'margin-bottom: 5px; font-weight: bold;']
            ])
            ->add('definicion', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Definición...', 'rows' => 3, 'class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Palabra::class,
        ]);
    }
}
