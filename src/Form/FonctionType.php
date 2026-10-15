<?php

namespace App\Form;

use App\Entity\Fonction;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FonctionType extends AbstractType
{
    public function __construct(
        private Security $security
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = ($options['user']) ?? $this->security->getUser();
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])->add('dateArchived', DateType::class, [
                'label' => 'Archiver la fonction',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fonction::class,
        ]);
    }
}
