<?php
/**
 * RestaurantType.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Form
 */
namespace App\Form;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantType extends AbstractType
{

    public function __construct(
        private Security $security
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = ($options['user']) ?? null;
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ]);
        $builder->add('address', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('zipCode', IntegerType::class, [
                'label' => 'Code postal',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ]);

            if ($this->security->isGranted(User::ROLE_ADMIN) === true) {
                $optionsUserField = [
                'label' => 'Propiétaire',
                'class' => User::class,
                'placeholder' => 'Sélectionner un Propiétaire',
                'autocomplete' => true,
                'no_results_found_text' => 'Aucun Propiétaire trouvé',
                'query_builder' => function (UserRepository $repository) {
                            return $repository->getUsersListInputByRole(User::ROLE_RETAURANT_OWNER);
                    }
                ];
            if ($user !== null) {
                $optionsUserField['data'] = $user;
            }
            $builder->add('owner', EntityType::class, $optionsUserField);
                $builder->add('dateArchived', DateType::class, [
                    'label' => 'Archiver la fonction',
                    'required' => false,
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
