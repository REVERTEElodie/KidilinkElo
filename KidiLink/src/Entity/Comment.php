<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get_comments_collection', 'get_comment_item'])]
    private ?int $id = null;
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['get_comments_collection', 'get_comment_item'])]
    private ?string $content = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Photo $photo = null;
    //Fonction construct pour formater les dates de créations et de modifications.
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }
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
    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }
    public function setPhoto(?Photo $photo): static
    {
        $this->photo = $photo;
        return $this;
    }
}
