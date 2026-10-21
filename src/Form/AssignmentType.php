<?php
/**
 * AssignmentType.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Form
 */
namespace App\Form;

use App\Entity\Assignment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            ->add('dateStart', DateType::class,
            [
                'label' => 'Date de début',
                'attr' => [
                    'data-controller' => 'datepicker'
                ],

            ])
            ->add('dateEnd', DateType::class,
            [
                'label' => 'Date de fin',
                'attr' => [
                    'data-controller' => 'datepicker'
                ],
            ]);
            $optionsUserField = [
                'label' => 'Collaborateur',
                'class' => User::class,
                'placeholder' => 'Sélectionner un collaborateur',
                'autocomplete' => true,
                'no_results_found_text' => 'Aucun collaborateurs trouvé',
                'query_builder' => function (UserRepository $repository) {
                        return $repository->getUsersListInputByRole(User::ROLE_COLLAB);
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
            'data_class' => Assignment::class,
            'user' => null
        ]);
        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
