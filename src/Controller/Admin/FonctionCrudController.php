<?php

namespace App\Controller\Admin;

use App\Entity\Fonction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
        ];
    }


    public function configureCrud(Crud $crud): Crud
    {
        $crud =  parent::configureCrud($crud);
        $crud->setPageTitle('index', 'Administration : Liste des fonctions')
        ->setPageTitle('detail', 'Administration : détail d\'une fonction')
        ->setPageTitle('new', 'Administration : Créer une fonction')
        ->setPageTitle('edit', 'Administration : Modifier une fonction');
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

        return $actions;
    }

}
