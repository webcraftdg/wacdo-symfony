<?php

namespace App\Form;

use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentSearchType extends AbstractType
{

    public function __construct(
        private Security $security
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'label' => 'Collaborateur',
                'class' => User::class,
                'placeholder' => 'Sélectionner un collaborateur',
                'required' => false,
                'query_builder' => function (UserRepository $repository) {
                        return $repository
                            ->createQueryBuilder('u')
                            ->andWhere('u.roles LIKE :role')
                            ->setParameter('role', '%ROLE_COLLAB%')
                            ->orderBy('u.lastname', 'ASC');
                }
            ])
            ->add('restaurant', EntityType::class, [
                'label' => 'Restaurant',
                'class' => Restaurant::class,
                'required' => false,
                'placeholder' => 'Sélectionner un restaurant',
                'query_builder'=> function(RestaurantRepository $repository) {
                     return $repository->createBuilderForUser($this->security->getUser());
                }
            ])->add('fonction', EntityType::class, [
                'label' => 'Fonction',
                'class' => Fonction::class,
                'required' => false,
                'placeholder' => 'Sélectionner une fonction'
            ])->add('dateStart', DateTimeType::class,
                [
                    'label' => 'Date de début',
                    'required' => false,
            ])->add('dateEnd', DateTimeType::class,
                [
                    'label' => 'Date de début',
                    'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
