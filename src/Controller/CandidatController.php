<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Candidat;
use App\Form\CandidatFormType;
use App\Repository\UserRepository;
use App\Service\EmptyArrayService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_CANDIDAT')]
#[Route('/candidat')]
class CandidatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerService $mailerService,
        private UserRepository $userRepo,
        private EmptyArrayService $array
    ) {
    }

    #[Route('/', name: 'app_candidat')]
    public function index($candidatures = null, $applies = null): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        } else {

            $candidats = $user->getCandidats();
            foreach ($candidats as $candidat) {
                $applies = $candidat->getApplies();
                foreach ($applies as $apply) {
                    if (!$apply->isIsValidated()) {
                        $this->addFlash('danger', 'Votre candidature de ' . $apply->getJobOffer()->getTitle() . ' n\'a pas été validé par nos équipes.');
                    }
                    $candidatures = $apply->getJobOffer();
                }
            }
        }

        return $this->render('candidat/index.html.twig', [
            'candidats' => $candidats,
            'candidatures' => $candidatures,
            'applies' => $applies,
        ]);
    }

    #[Route('/profil', name: 'profilCandidat')]
    public function profil(Request $request, SluggerInterface $slugger, Candidat $candidat = null): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        } else {


            if (!$candidat) {
                $candidat = new Candidat();
            }
            $candidat->setUser($this->getUser());
            $candidat->setisValidated(true);

            $form = $this->createForm(CandidatFormType::class, $candidat);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $cvFile = $form->get('cv')->getData();
                if ($cvFile) {
                    $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                    try {
                        $cvFile->move(
                            $this->getParameter('cv_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('alert', 'Une erreur est survenue, dépôt de CV obligatoire.');
                    }
                    $candidat->setCv($newFilename);
                }

                $this->em->persist($candidat);
                $this->em->flush();

                $this->addFlash('success', 'Profil modifié, vous pouvez maintenant postuler à une annonce');
                return $this->redirectToRoute('app_candidat');
            }
        }
        return $this->render('candidat/profil.html.twig', [
            'CandidatFormType' => $form->createView(),
            'candidat' => $candidat,
            'editMode' => $candidat->getId() !== null,
        ]);
    }

    #[Route('/editer/{id}', name: 'editCandidat')]
    public function editProfil(Request $request, SluggerInterface $slugger, Candidat $candidat = null): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }
        if (!$candidat) {
            $candidat = new Candidat();
        }
        $candidat->setUser($this->getUser());

        $candidat->setCV('');

        $form = $this->createForm(CandidatFormType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$candidat->getId()) {
            }
            $cvFile = $form->get('cv')->getData();
            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();
                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('alert', 'Une erreur est survenue, dépôt de CV obligatoire !!');
                }
                $candidat->setCv($newFilename);
            }

            $candidat->setIsValidated(true);
            $this->em->persist($candidat);
            $this->em->flush();

            $this->addFlash('success', 'Profil modifié');
            return $this->redirectToRoute('app_candidat');
        }

        return $this->render('candidat/profil.html.twig', [
            'CandidatFormType' => $form->createView(),
            'editMode' => $candidat->getId() !== null,
            'candidat' => $candidat
        ]);
    }
}
