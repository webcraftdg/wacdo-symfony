<?php

namespace App\Form;

use App\Entity\Assigment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Enum\AssisnmentStatus;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentType extends AbstractType
{

    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = ($options['user']) ?? null;
        $builder
            ->add('dateStart', DateTimeType::class,
            [
                'label' => 'Date de début',

            ])
            ->add('dateEnd', DateTimeType::class,
            [

            ]);
            $optionsUserField = [
                'label' => 'Collaborateur',
                'class' => User::class,
                'placeholder' => 'Sélectionner un collaborateur',
                'query_builder' => function (UserRepository $repository) {
                        return $repository
                            ->createQueryBuilder('u')
                            ->andWhere('u.roles LIKE :role')
                            ->setParameter('role', '%ROLE_COLLAB%')
                            ->orderBy('u.lastname', 'ASC');
                }
            ];
            if ($user !== null) {
                $optionsUserField['data'] = $user;
            }
            $builder->add('user', EntityType::class, $optionsUserField)
            ->add('restaurant', EntityType::class, [
                'label' => 'Restaurant',
                'class' => Restaurant::class,
                'placeholder' => 'Sélectionner un restaurant',
                'query_builder'=> function(RestaurantRepository $repository) {
                     return $repository->createBuilderForUser($this->security->getUser());
                }
            ])
            ->add('fonction', EntityType::class, [
                'label' => 'Fonction',
                'class' => Fonction::class,
                'placeholder' => 'Sélectionner une fonction'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assigment::class,
            'user' => null
        ]);
        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
