<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrickRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
#[UniqueEntity('name')]
class Trick implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\Type('int')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\Type('string')]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[Assert\Type(Category::class)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[Assert\Type(User::class)]
    private ?User $author = null;

    #[ORM\OneToMany(
        targetEntity: Picture::class,
        mappedBy: 'trick',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[Assert\All(
        new Assert\Type(Picture::class)
    )]
    private Collection $pictures;

    #[ORM\OneToMany(
        targetEntity: Video::class,
        mappedBy: 'trick',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[Assert\All(
        new Assert\Type(Video::class)
    )]
    private Collection $videos;

    #[ORM\OneToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Assert\Type(Picture::class)]
    private ?Picture $mainPicture = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(
        targetEntity: Comment::class,
        mappedBy: 'trick',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[Assert\All(
        new Assert\Type(Comment::class)
    )]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    private ?Difficulty $difficulty = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();

        $this->setCreatedAt(new \DateTimeImmutable());
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getMainPicture(): ?Picture
    {
        return $this->mainPicture;
    }

    public function setMainPicture(?Picture $picture): static
    {
        $this->mainPicture = $picture;

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setTrick($this);

            if (is_null($this->getMainPicture())) {
                $this->setMainPicture($picture);
            }
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getTrick() === $this) {
                $picture->setTrick(null);

                // Reset the new main picture
                if ($this->getMainPicture() === $picture) {
                    $this->setMainPicture($this->pictures->first() ?: null);
                }
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getDifficulty(): ?Difficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(?Difficulty $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
