<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('keyword', TextType::class, [
                'label' => 'Rechercher',
                'required' => false,
                'attr' => [
                    'placeholder' => ($options['placeholder']) ?? 'Rechercher ...',
                ],
                'label_attr' => [
                    'class' => 'text-left'
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'placeholder' => 'Rechercher...',
            'method' => 'POST',
        ]);

        $resolver->setAllowedTypes('placeholder', 'string');
    }
}
