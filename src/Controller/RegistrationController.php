<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private JWTService $jwt,
        private MailerService $mailerService,
    ) {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $candidats = $user->getCandidats();
            foreach ($candidats as $candidat) {
                $user->addCandidat($candidat);
            }

            $recruiters = $user->getRecruiters();
            foreach ($recruiters as $recruiter) {
                $user->addRecruiter($recruiter);
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->mailerService->send(
                $user->getEmail(),
                'Demande de création de compte',
                'registration_confirmation.html.twig',
                [
                    'user' => $user,
                    'token' => null,
                ]
            );

            $this->addFlash('success', 'Votre demande de création de compte à bien été envoyée. Une fois validée, vous receverez un mail pour l\'activer.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepo): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            $payload = $jwt->getPayload($token);

            $user = $userRepo->find($payload['user_id']);

            if ($user && $user->isIsValidated()) {
                $this->addFlash('success', 'Bienvenue !! <br> Connectez-vous pour accéder à votre compte');
                return $this->redirectToRoute('app_login');
            }
        }
        $this->addFlash('danger', 'Le lien d\'activation est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
