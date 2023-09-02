<?php

namespace App\Controller;

use App\Entity\Apply;
use App\Entity\JobOffer;
use App\Entity\Recruiter;
use App\Entity\User;
use App\Repository\ApplyRepository;
use App\Repository\JobOfferRepository;
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
        private JobOfferRepository $jobOfferRepo,
        private ApplyRepository $applyRepo,
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

    #[Route('/validation-offre', name: 'valid_jobOffer')]
    public function actifJobOffer(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } else {

            $jobOffers = $this->jobOfferRepo->findBy(['isValidated' => false]);

            return $this->render('consultant/jobOfferActive.html.twig', [
                'jobOffers' => $jobOffers,
            ]);
        }
    }

    #[Route('/activer/offre/{id}', name: 'active_joboffer')]
    public function activedJobOffer(JobOffer $jobOffer, User $user): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } else {

            $jobOffer->setIsValidated(true);
            $mail = $jobOffer->getRecruiter()->getUser()->getEmail();
            $name = $jobOffer->getRecruiter()->getCompanyName();
            $this->em->flush();
            $this->addFlash('success', 'Annonce activée');

            $this->mailerService->send(
                $mail,
                'Votre Annonce a bien été activée',
                'activeJobOffer.html.twig',
                [
                    'user' => $user,
                    'name' => $name,
                ]
            );
            $this->addFlash('info', 'Un email d\'activation a bien été envoyé.');
            return $this->redirectToRoute('valid_jobOffer');
        }
    }

    #[Route('/validation-candidature', name: 'valid_apply')]
    public function actifApply(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } else {

            $applies = $this->applyRepo->findBy(['isValidated' => false]);

            return $this->render('consultant/applyActive.html.twig', [
                'applies' => $applies,
            ]);
        }
    }

    #[Route('/activer/candidature/{id}', name: 'active_apply')]
    public function activedApply(Apply $apply): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        } else {
            $apply->setIsValidated(true);

            $this->em->persist($apply);
            $this->em->flush();
            $this->addFlash('success', 'Candidature acceptée');

            $mail = $apply->getJobOffer()->getRecruiter()->getUser()->getEmail();
            $recruiter = $apply->getJobOffer()->getRecruiter();
            $jobOffer = $apply->getJobOffer();
            $candidat = $apply->getCandidat();
            $apply = $this->applyRepo;

            $this->mailerService->send(
                $mail,
                'Nouvelle Candidature',
                'apply.html.twig',
                [
                    'recruiter' => $recruiter,
                    'candidat' => $candidat,
                    'jobOffer' => $jobOffer,
                    'apply' => $apply,
                ]
            );
            $this->addFlash('info', 'Un email a bien été envoyé au recruteur.');
            return $this->redirectToRoute('valid_apply');
        }
    }

    #[Route('/supprimer-candidature/{id}', name: 'remove_apply')]
    public function removeApply(Apply $apply, ApplyRepository $applyRepo): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        } else {

            $applyRepo->remove($apply);

            $this->em->flush();
            $this->addFlash('success', 'Candidature supprimée');

            $mail = $apply->getCandidat()->getUser()->getEmail();
            $jobOffer = $apply->getJobOffer();
            $candidat = $apply->getCandidat();

            $this->mailerService->send(
                $mail,
                'Candidature refusée',
                'removeApply.html.twig',
                [
                    'candidat' => $candidat,
                    'jobOffer' => $jobOffer,
                ]
            );
            $this->addFlash('info', 'Un email a bien été envoyé au candidat.');
            return $this->redirectToRoute('valid_apply');
        }
    }
}
