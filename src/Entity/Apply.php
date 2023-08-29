<?php

namespace App\Entity;

use App\Repository\ApplyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplyRepository::class)]
class Apply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne(targetEntity: Candidat::class, inversedBy: 'applies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidat $candidat = null;

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: 'Applies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): static
    {
        $this->jobOffer = $jobOffer;

        return $this;
    }
}
