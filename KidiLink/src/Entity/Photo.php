<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PhotoRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?int $likes = null;

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

    #[ORM\ManyToOne(inversedBy: 'photos')]
    //super important pour ne pas faire buguer les tables avec les clés étrangères: 
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Groups(['get_photos_collection', 'get_photo_item'])]
    private ?Album $album = null;

    #[ORM\OneToMany(mappedBy: 'photo', targetEntity: Comment::class, orphanRemoval: true)]

    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn( nullable: false, name: 'album_id', referencedColumnName: 'id', onDelete: "CASCADE")]
    private Classe $class;

    public function getClass(): ?Classe
    {
        return $this->class;
    }

    public function setClass(?Classe $class): self
    {
        $this->class = $class;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): static
    {
        $this->likes = $likes;

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

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPhoto($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
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
