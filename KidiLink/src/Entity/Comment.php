<?php
namespace App\Entity;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;
            //Fonction construct pour formater les dates de créations et de modifications.
            public function __construct()
            {
                $this->created_at = new \DateTimeImmutable();
                $this->updated_at = new \DateTimeImmutable();
                $this->comments = new ArrayCollection();
            }
    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?self $photo = null;
    #[ORM\OneToMany(mappedBy: 'photo', targetEntity: self::class)]
    private Collection $comments;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(string $content): static
    {
        $this->content = $content;
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
    public function getPhoto(): ?self
    {
        return $this->photo;
    }
    public function setPhoto(?self $photo): static
    {
        $this->photo = $photo;
        return $this;
    }
    /**
     * @return Collection<int, self>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
    public function addComment(self $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPhoto($this);
        }
        return $this;
    }
    public function removeComment(self $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPhoto() === $this) {
                $comment->setPhoto(null);
            }
        }
        return $this;
    }
}