<?php

namespace App\Form;

use App\Entity\Assigment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Enum\AssisnmentStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateStart', DateTimeType::class,
            [
                'label' => 'Date de début'
            ])
            ->add('dateEnd', DateTimeType::class,
            [
                'label' => 'Date de début'
            ])
            ->add('status', EnumType::class, [
                'label' => 'Statut',
                'class' => AssisnmentStatus::class,
                'choice_label' => function (AssisnmentStatus $status): string {
                    return match ($status) {
                        AssisnmentStatus::EN_ATTENTE => 'En attente',
                        AssisnmentStatus::EN_COURS => 'En cours',
                        AssisnmentStatus::REFUSER => 'Refuser',
                        AssisnmentStatus::VALIDER => 'Valider',
                    };
                },
            ])
            ->add('user', EntityType::class, [
                'label' => 'Collaborateur',
                'class' => User::class,
            ])
            ->add('restaurant', EntityType::class, [
                'label' => 'Restaurant',
                'class' => Restaurant::class,
            ])
            ->add('fonction', EntityType::class, [
                'label' => 'Fonction',
                'class' => Fonction::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assigment::class,
        ]);
    }
}
