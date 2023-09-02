<?php

namespace App\Controller;

use App\Entity\Recruiter;
use App\Form\RecruiterFormType;
use App\Service\EmptyArrayService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_RECRUITER')]
#[Route('/recruteur')]
class RecruiterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerService $MailerService,
        private EmptyArrayService $array,
    ) {
    }

    #[Route('/', name: 'app_recruiter')]
    public function index($jobOffers = null, $candidatures = null): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        $recruiters = $user->getRecruiters();
        foreach ($recruiters as $recruiter) {

            $jobOffers = $recruiter->getJobOffers();
            if ($jobOffers != null) {
                foreach ($jobOffers as $jobOffer) {
                    $candidatures = $jobOffer->getApplies();
                }
            } else {
                $jobOffers = null;
            }
        }

        return $this->render('recruiter/index.html.twig', [
            'user' => $user,
            'recruiters' => $recruiters,
            'jobOffers' => $jobOffers,
            'candidatures' => $candidatures,
        ]);
    }

    #[Route('/profil', name: 'profilRecruiter')]
    public function profil(Request $request, Recruiter $recruiter = null, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accédeer à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$recruiter) {
            $recruiter = new Recruiter();
        }
        $recruiter->setUser($this->getUser());
        $recruiter->setIsValidated(true);
        $form = $this->createForm(RecruiterFormType::class, $recruiter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($recruiter);
            $entityManager->flush();

            $this->addFlash('success', 'Profil complété, vous pouvez maintenant créer une annonce.');
            return $this->redirectToRoute('app_recruiter');
        }
        return $this->render('recruiter/profil.html.twig', [
            'recruiterForm' => $form->createView(),
            'editMode' => $recruiter->getId() !== null,
            'recruiter' => $recruiter
        ]);
    }

    #[Route('/editer/{id}', name: 'editRecruiter')]
    public function editProfil(Request $request, Recruiter $recruiter = null): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$recruiter) {
            $recruiter = new Recruiter();
        }
        $recruiter->setUser($this->getUser());

        $form = $this->createForm(RecruiterFormType::class, $recruiter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$recruiter->getId()) {
            }
            $recruiter->setIsValidated(true);
            $this->em->persist($recruiter);
            $this->em->flush();

            $this->addFlash('success', 'Profil modifié');
            return $this->redirectToRoute('app_recruiter');
        }

        return $this->render('recruiter/profil.html.twig', [
            'recruiterForm' => $form->createView(),
            'editMode' => $recruiter->getId() !== null,
            'recruiter' => $recruiter
        ]);
    }
}
