<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Carburant;
use App\Entity\Prestation;
use App\Controller\Admin\UserCrudController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/dashboard_admin', routeName: 'dashboard_admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {   
        $adminUrl = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrl->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Projet2jc')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-home', 'app_home');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);    
        yield MenuItem::linkToCrud('Carburants', 'fas fa-gas-pump', Carburant::class);
        yield MenuItem::linkToCrud('Prestations', 'fas fa-wrench', Prestation::class);
    }

}
