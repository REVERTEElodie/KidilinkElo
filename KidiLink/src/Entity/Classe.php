<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClasseRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
#[UniqueEntity('name')]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 64)]
    #[ASSERT\NotBlank()]
    private ?string $name = null;

    #[ORM\Column(length: 18)]
    private ?string $annee_scolaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    //Fonction construct pour formater les dates de créations et de modifications.
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;


    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAnneeScolaire(): ?string
    {
        return $this->annee_scolaire;
    }

    public function setAnneeScolaire(string $annee_scolaire): static
    {
        $this->annee_scolaire = $annee_scolaire;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
