<?php

namespace App\Controller\Admin;

use App\Entity\Assigment;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AssigmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assigment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud =  parent::configureCrud($crud);
        $crud->setPageTitle('index', 'Administration : Liste des affectations')
        ->setPageTitle('detail', 'Administration : détail d\'une affectation')
        ->setPageTitle('new', 'Administration : Créer une affectation')
        ->setPageTitle('edit', 'Administration : Modifier une affectation');
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
            DateField::new('dateStart')->setLabel('Date de début')->setFormat('dd/MM/Y'),
            DateField::new('dateEnd')->setLabel('Date de fin')->setFormat('dd/MM/Y'),
            AssociationField::new('user')
                ->setLabel('Collaborateur')
                ->setQueryBuilder(
                    fn (QueryBuilder $qb) => $qb
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_COLLAB%')
                        ->orderBy('entity.lastname', 'ASC')),
            AssociationField::new('restaurant')
                ->setLabel('Restaurant'),
            AssociationField::new('fonction')
                ->setLabel('Fonction'),
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
            return $action->setLabel('Ajouter une affectation');
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
