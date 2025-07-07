<?php

namespace App\Controller\SuperAdmin;

use App\Entity\Carburant;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CarburantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carburant::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
