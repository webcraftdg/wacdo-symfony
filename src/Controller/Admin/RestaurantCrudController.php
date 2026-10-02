<?php

namespace App\Controller\Admin;

use App\Entity\Restaurant;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }


    public function deleteEntity(
        EntityManagerInterface $entityManager,
        object $entityInstance): void
    {
        if ($entityInstance instanceof Restaurant) {
            $entityInstance->setDateArchived(new DateTime());
            $entityManager->flush();
        }
    }


    public function configureCrud(Crud $crud): Crud
    {
        $crud =  parent::configureCrud($crud);
        $crud->setPageTitle('index', 'Administration : Liste des restaurant')
        ->setPageTitle('detail', 'Administration : détail d\'un restaurant')
        ->setPageTitle('new', 'Administration : Créer un restaurant')
        ->setPageTitle('edit', 'Administration : Modifier un restaurant');
        //Ajout des groups selon l'action ('new', 'edit')
        $crud->setFormOptions(
            ['validation_groups' => ['Default', 'create']], // Action NEW
            ['validation_groups' => ['Default']]            // EDIT
        );
        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            AssociationField::new('owner')->setLabel('Propriétaire')
                ->setQueryBuilder(
                    fn (QueryBuilder $qb) => $qb
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_RESTAURANT_OWNER%')
                        ->orderBy('entity.lastname', 'ASC')),
            TextField::new('address'),
            NumberField::new('zipCode')->onlyOnForms(),
            TextField::new('postalCode')->onlyOnDetail(),
            TextField::new('city'),
            DateTimeField::new('dateArchived')->setLabel('Date archivée')->setFormat('dd/MM/Y'),
        ];
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
            return $action->setLabel('Ajouter un restaurant');
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
