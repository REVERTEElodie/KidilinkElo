<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get_users_collection', 'get_user_item'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['get_users_collection', 'get_user_item'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['get_users_collection', 'get_user_item'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get_users_collection', 'get_user_item'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get_users_collection', 'get_user_item'])]
    private ?string $lastname = null;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: Classe::class )]
    private Collection $classesManaged;

    #[ORM\ManyToMany(targetEntity: Classe::class, inversedBy: 'parents')]
    private Collection $classes;

    public function __construct()
    {
        $this->classesManaged = new ArrayCollection();
        $this->classes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Ajoutez le code pour effacer les informations temporaires liées à l'authentification
        // par exemple, $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstname;
    }

    public function setFirstName(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastname;
    }

    public function setLastName(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    // Si vous avez besoin d'accéder au mot de passe en clair, implémentez les méthodes
    // getPlainPassword() et setPlainPassword() en conséquence.

    /**
     * @return Collection<int, Classe>
     */
    public function getClassesManaged(): Collection
    {
        return $this->classesManaged;
    }

    public function addClassesManaged(Classe $classesManaged): static
    {
        if (!$this->classesManaged->contains($classesManaged)) {
            $this->classesManaged->add($classesManaged);
            $classesManaged->setManager($this);
        }

        return $this;
    }

    public function removeClassesManaged(Classe $classesManaged): static
    {
        if ($this->classesManaged->removeElement($classesManaged)) {
            // set the owning side to null (unless already changed)
            if ($classesManaged->getManager() === $this) {
                $classesManaged->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Classe>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(Classe $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
        }

        return $this;
    }

    public function removeClass(Classe $class): static
    {
        $this->classes->removeElement($class);

        return $this;
    }
}
