<?php

namespace App\Controller;

use App\Entity\Apply;
use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidature')]
class ApplyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ManagerRegistry $doctrine,
    ) {
    }

    #[Route('/', name: 'app_apply')]
    public function index(): Response
    {
        $repository = $this->doctrine->getRepository(Candidat::class);
        $candidats = $repository->findAll();

        return $this->render('apply/index.html.twig', [
            'candidats' => $candidats,
        ]);
    }

    #[IsGranted('ROLE_CANDIDAT')]
    #[Route('/postuler/{id}', name: 'apply')]
    public function test(CandidatRepository $candidatRepo, JobOfferRepository $jobOfferRepo, int $id): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $candidats = $user->getCandidats();

        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        $apply = new Apply();
        //Recup id_joboffer
        $jobOffer = $jobOfferRepo->find($id);
        $apply->setJobOffer($jobOffer);
        $jobOffer->addApply($apply);

        //lier candidate à apply
        foreach ($candidats as $candidat) {
            $idCandidat = $candidat->getId();
            $candidat = $candidatRepo->find($idCandidat);
            $apply->setCandidat($candidat);
            $candidat->addApply($apply);
        }

        $candApply = $apply->getCandidat();
        if ($candApply === null) {
            $this->addFlash('danger', 'Vous ne pouvez pas postuler à cette annonce, votre profil n\'est pas à jour.');

            return $this->redirectToRoute('profilCandidat');
        } else {
            $this->em->persist($apply);
            $this->em->flush();
            $this->addFlash('success', 'Votre candidature a bien été enregistrée.');

            return $this->redirectToRoute('jobOffer');
        }
    }
}
