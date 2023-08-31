<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferFormType;
use App\Repository\JobOfferRepository;
use App\Repository\RecruiterRepository;
use App\Service\EmptyArrayService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_RECRUITER') and is_granted('ROLE_CANDIDAT')")]
#[Route('/offre')]
class JobOfferController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobOfferRepository $jobOfferRepo,
        private MailerService $MailerService,
        private EmptyArrayService $array,
    ) {
    }

    #[Route('/', name: 'app_jobOffer')]
    public function index(): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->addFlash('alert', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $jobOffers = $this->jobOfferRepo->findBy(['isValidated' => true]);

        return $this->render('jobOffer/index.html.twig', [
            'joboffers' => $jobOffers,
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/créer', name: 'createApply')]
    public function newJobOffer(Request $request, RecruiterRepository $recruiterRepo, JobOffer $jobOffer = null): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->addFlash('alert', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        $recruiters = $user->getRecruiters();
        $jobOffer = new JobOffer();

        foreach ($recruiters as $recruiter) {

            $id = $recruiter->getId();
            $recruiter = $recruiterRepo->find($id);
            $jobOffer->setRecruiter($recruiter);
            $recruiter->addJobOffer($jobOffer);
        }

        $form = $this->createForm(JobOfferFormType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $this->em->persist($jobOffer);
            $this->em->flush();
            $this->addFlash('success', 'Votre annonce est en cours de vérification, vous recevrez un email lorsqu\'elle sera active.');
            return $this->redirectToRoute('app_recruiter');
        }

        return $this->render('jobOffer/createJobOffer.html.twig', [
            'JobOfferFormType' => $form->createView(),
            'recruiters' => $recruiters,
            'editMode' => $jobOffer->getId() !== null,
        ]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/modifier/{id}', name: 'editApply')]
    public function editJobOffer(Request $request, JobOffer $jobOffer = null): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->addFlash('alert', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        $recruiters = $user->getRecruiters();
        if (!$jobOffer) {
            $jobOffer = new JobOffer();
        }

        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        $jobOffer->setIsValidated(false);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($jobOffer);
            $this->em->flush();
            $this->addFlash('success', 'Votre annonce a bien été modifiée et doit être validée par un de nos consultants.');
            return $this->redirectToRoute('app_recruiter');
        }

        return $this->render('jobOffer/createJobOffer.html.twig', [
            'JobOfferFormType' => $form->createView(),
            'recruiters' => $recruiters,
            'editMode' => $jobOffer->getId() !== null,
        ]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/supprimer/{id}', name: 'removeApply')]
    public function removeJobOffer(JobOffer $jobOffer, JobOfferRepository $jobRepo): Response
    {
        /**
         * @var User $user
         */
        if (!$this->getUser()) {
            $this->addFlash('alert', 'Vous devez être connecté pour accéde à cette page.');
            return $this->redirectToRoute('app_login');
        } else {

            $jobRepo->remove($jobOffer);
            $this->em->flush();
            $this->addFlash('success', 'Votre annonce a bien été supprimée.');

            return $this->redirectToRoute('app_recruiter');
        }
    }
}
