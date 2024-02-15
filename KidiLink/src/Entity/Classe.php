<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClasseRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
#[UniqueEntity('name')]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get_classes_collection', 'get_class_item'])]
    private int $id;

    #[ORM\Column(length: 64)]
    #[ASSERT\NotBlank()]
    #[Groups(['get_classes_collection', 'get_class_item'])]
    private ?string $name = null;

    #[ORM\Column(length: 18)]
    #[Groups(['get_classes_collection', 'get_class_item'])]
    private ?string $annee_scolaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    //Fonction construct pour formater les dates de créations et de modifications.
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
        $this->albums = new ArrayCollection();
        $this->parents = new ArrayCollection();
    }

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'classe', targetEntity: Album::class)]
    private Collection $albums;

    #[ORM\ManyToOne(inversedBy: 'classesManaged')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?User $manager = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'classes')]
    private Collection $parents;


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

    /**
     * @return Collection<int, Album>
     */
    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): static
    {
        if (!$this->albums->contains($album)) {
            $this->albums->add($album);
            $album->setClasse($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): static
    {
        if ($this->albums->removeElement($album)) {
            // set the owning side to null (unless already changed)
            if ($album->getClasse() === $this) {
                $album->setClasse(null);
            }
        }

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(User $parent): static
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->addClass($this);
        }

        return $this;
    }

    public function removeParent(User $parent): static
    {
        if ($this->parents->removeElement($parent)) {
            $parent->removeClass($this);
        }

        return $this;
    }
}
