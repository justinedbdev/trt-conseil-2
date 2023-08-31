<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin', name: 'dashboard')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TRT Conseil');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Tableau de bord');

        yield MenuItem::linkToDashboard('Homepage', 'fa fa-home', 'homepage');

        yield MenuItem::section('Compte Consultant');

        yield MenuItem::subMenu('Créer compte consultant', 'fas fa-bars')
            ->setSubItems([
                MenuItem::linkToCrud('Créer compte consultant', 'fas fa-plus', User::class)
                    ->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Afficher les comptes consultant', 'fas fa-eye', User::class)
            ]);
    }
}
