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
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
        $user = ($options['user']) ?? $this->security->getUser();
        $restaurant = ($options['restaurant']) ?? null;
        $builder
            ->add('keyword', TextType::class, [
                'label' => 'Rechercher',
                'required' => false,
               'attr' => [
                    'placeholder' => ($options['placeholder']) ?? 'Rechercher ...',
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
                    'placeholder' => 'Chercher un collaborateur',
                    'required' => false,
                    'autocomplete' => true,
                    'no_results_found_text' => 'Aucun collaborateurs trouvé',
                    'query_builder' => function (UserRepository $repository) use ($user) {
                            return  $repository->createCollaboratorQuery($user);
                    }
                ]);
            }
            if ($restaurant === null) {
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
                ]);
            }
            $builder->add('fonction', EntityType::class, [
                'label' => 'Fonction',
                'class' => Fonction::class,
                'label_attr' => [
                    'class' => 'text-left'
                ],
                'required' => false,
                'placeholder' => 'Sélectionner une fonction'
            ])->add('dateStart', DateType::class,
                [
                    'label' => 'Date de début',
                    'label_attr' => [
                        'class' => 'text-left'
                    ],
                    'attr' => [
                        'data-controller' => 'datepicker'
                    ],
                    'required' => false,
            ])->add('dateEnd', DateType::class,
                [
                    'label' => 'Date de début',
                    'label_attr' => [
                        'class' => 'text-left'
                    ],
                    'attr' => [
                        'data-controller' => 'datepicker'
                    ],
                    'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'placeholder' => 'Rechercher...',
            'user' => null,
            'restaurant' => null,
            'csrf_protection' => true,
        ]);
        $resolver->setAllowedTypes('placeholder', 'string');
        $resolver->setAllowedTypes('user', ['null', User::class]);
        $resolver->setAllowedTypes('restaurant', ['null', Restaurant::class]);
    }
}
