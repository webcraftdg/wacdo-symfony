<?php

namespace App\Controller\Admin;

use App\Entity\Fonction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FonctionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Fonction::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            DateTimeField::new('dateArchived')->setLabel('Date archivée')->setFormat('dd/MM/Y')
        ];
    }

    public function deleteEntity(
        EntityManagerInterface $entityManager,
        object $entityInstance): void
    {
        if ($entityInstance instanceof Fonction) {
            $entityInstance->setDateArchived(new DateTime());
            $entityManager->flush();
        }
    }


    public function configureCrud(Crud $crud): Crud
    {
        $crud =  parent::configureCrud($crud);
        $crud->setPageTitle(Action::INDEX, 'Administration : Liste des fonctions')
        ->setPageTitle(Action::DETAIL, 'Administration : détail d\'une fonction')
        ->setPageTitle(Action::NEW, 'Administration : Créer une fonction')
        ->setPageTitle(Action::EDIT, 'Administration : Modifier une fonction');
        //Ajout des groups selon l'action ('new', 'edit')
        $crud->setFormOptions(
            ['validation_groups' => ['Default', 'create']], // Action NEW
            ['validation_groups' => ['Default']]            // EDIT
        );
        return $crud;
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
            return $action->setLabel('Ajouter une fonction');
        });
        $actions->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Enregistrer');
        });
        $actions->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Enregistrer et Ajouter');
        });
        $actions->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Enregistrer');
        });
        $actions->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
            return $action->setLabel('Enregistrer et Ajouter');
        });

        return $actions;
    }

}
