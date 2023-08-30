<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CONSULTANT')]
#[Route('/consultant')]
class ConsultantController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepo,
        private EntityManagerInterface $em,
        private JWTService $jwt,
        private MailerService $mailerService,
    ) {
    }

    #[Route('/', name: 'app_consultant')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('consultant/index.html.twig');
        }
    }

    #[Route('/validation-compte', name: 'valid_account')]
    public function actifAccount(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } else {

            $accounts = $this->userRepo->findBy(['isValidated' => false]);

            $accountRoles = $this->userRepo->findAll();
            foreach ($accountRoles as $count) {
                $role = $count->getRoles();
                $roleStr = implode(" ", $role);
            }

            return $this->render('consultant/userAccount.html.twig', [
                'accounts' => $accounts,
                'role' => $roleStr,
            ]);
        }
    }

    #[Route('/activer/compte/{id}', name: 'active_user')]
    public function activedAccount(User $user): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } else {

            $user->setIsValidated(true);
            $this->em->flush();
            $this->addFlash('success', 'Le compte a été activé');

            $header = [
                'type' => 'JWT',
                'alg' => 'HS256'
            ];
            $payload = [
                'user_id' => $user->getId()
            ];
            $token = $this->jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            $this->mailerService->send(
                $user->getEmail(),
                'Activation de votre compte',
                'activeAccount.html.twig',
                [
                    'user' => $user,
                    'token' => $token,
                ]
            );
            $this->addFlash('info', 'Un email d\'activation a bien été envoyé.');
            return $this->redirectToRoute('valid_account');
        }
    }
}

# return $this->render('consultant/index.html.twig', [
#'controller_name' => 'ConsultantController',
#]);