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
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        /** @var User $user */
        $user = $this->security->getUser();
        $builder
            ->add('keyword', TextType::class, [
                'label' => 'Rechercher',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom collaborateur, restaurant, ville...',
                ],
                'label_attr' => [
                    'class' => 'text-left'
                ]
            ]);
            if ($user instanceof User && $user->getRole() !== User::ROLE_COLLAB) {
                $builder->add('user', EntityType::class, [
                    'label' => 'Collaborateur',
                    'class' => User::class,
                    'label_attr' => [
                        'class' => 'text-left'
                    ],
                    'placeholder' => 'Sélectionner un collaborateur',
                    'required' => false,
                    'query_builder' => function (UserRepository $repository) use ($user) {
                            return  $repository
                                ->findByUser($user)
                                ->andWhere('u.roles LIKE :role')
                                ->setParameter('role', '%ROLE_COLLAB%')
                                ->orderBy('u.lastname', 'ASC');
                    }
                ]);
            }

            $builder->add('restaurant', EntityType::class, [
                'label' => 'Restaurant',
                'class' => Restaurant::class,
                'label_attr' => [
                    'class' => 'text-left'
                ],
                'required' => false,
                'placeholder' => 'Sélectionner un restaurant',
                'query_builder'=> function(RestaurantRepository $repository) use ($user){
                     return $repository->createBuilderForUser($user);
                }
            ])->add('fonction', EntityType::class, [
                'label' => 'Fonction',
                'class' => Fonction::class,
                'label_attr' => [
                    'class' => 'text-left'
                ],
                'required' => false,
                'placeholder' => 'Sélectionner une fonction'
            ])->add('dateStart', DateTimeType::class,
                [
                    'label' => 'Date de début',
                    'label_attr' => [
                        'class' => 'text-left'
                    ],
                    'required' => false,
            ])->add('dateEnd', DateTimeType::class,
                [
                    'label' => 'Date de début',
                    'label_attr' => [
                        'class' => 'text-left'
                    ],
                    'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
        ]);
    }
}
