<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    /**
     * Constructor
     *
     * Ajout du User password Hasher
     *
     * @param  \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * persistEntity
     *
     * Fonctuon appelé par Crud avant le persist de l'entité
     *
     * @param  \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param  [type]                               $entityInstance
     *
     * @return void
     */
    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {
        if (!$entityInstance instanceof User) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $entityInstance,
            $entityInstance->getPlainPassword()
        );

        $entityInstance->setPassword($hashedPassword);
        $entityInstance->setRoles([$entityInstance->getRole()]);
        $entityInstance->setIsAdmin(false);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * updateEntity
     *
     * Fonctuon appelé par Crud avant l'update de l'entité
     *
     * @param  \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param  [type]                               $entityInstance
     *
     * @return void
     */
    public function updateEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void
    {
        if (
            $entityInstance instanceof User &&
            empty($entityInstance->getPlainPassword()) === false
        ) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPlainPassword()
                )
            );
        }
        $entityInstance->setIsAdmin(false);
        $entityInstance->setRoles([$entityInstance->getRole()]);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * createIndexQueryBuilder
     *
     * Personnaliser la Query d'appel des entité dans le controller et function index (list)
     *
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto               $searchDto
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto               $entityDto
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection  $fields
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection $filters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        CollectionFilterCollection $filters
        ): QueryBuilder
    {
        $query =  parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $query->andWhere('entity.isAdmin  = 0');
        return $query;
    }

    /**
     * configureCrud
     *
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Config\Crud $crud
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\Config\Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        $crud =  parent::configureCrud($crud);
        $crud->setPageTitle('index', 'Administration : Liste des utilisateurs')
        ->setPageTitle('detail', 'Administration : détail d\'un utilisateur')
        ->setPageTitle('new', 'Administration : Créer un utilisateur')
        ->setPageTitle('edit', 'Administration : Modifier un utilisateur');
        //Ajout des groups selon l'action ('new', 'edit')
        $crud->setFormOptions(
            ['validation_groups' => ['Default', 'user:create']], // Action NEW
            ['validation_groups' => ['Default']]            // EDIT
        );
        return $crud;
    }


    /**
     * configureFields
     *
     * Paramètrage des champs
     *
     * @param  string   $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        $fields =  [
            EmailField::new('email')->setLabel('Email'),
            TextField::new('lastname')->setLabel('Nom'),
            TextField::new('firstname')->setLabel('Prénom'),
            DateField::new('dateRecruitment')->setLabel('Date de recrutement')->setFormat('dd/MM/Y'),
        ];

        //Afficher les champs pour les action 'edit' et 'new'
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT]) === true) {
            $fields[] = TextField::new('plainPassword')
            ->setFormType(RepeatedType::class) // permet d'avoir 2 champs dont un de vérification
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
            ])
            ->onlyOnForms(); // Seleument visible dans le  ou les forms
        }

        $fields[] =        ChoiceField::new('role')
            ->setLabel('Droit')
            ->setChoices(User::getAvailableRoles())
            ->setFormTypeOption('placeholder', 'Choisissez un droit')
            ->allowMultipleChoices(false)->autocomplete();

        return $fields;
    }


    /**
     * configureActions
     *
     * Paramétrer les intitulés des boutons ...
     *
     * @param  \EasyCorp\Bundle\EasyAdminBundle\Config\Actions $actions
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\Config\Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions->update(Crud::PAGE_INDEX, Action::NEW, function(Action $action) {
            return $action->setLabel('Ajouter un utilisateur');
        });
        $actions->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Enregistrer');
        });
        $actions->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Enregistrer et Ajouter');
        });

        return $actions;
    }

}
