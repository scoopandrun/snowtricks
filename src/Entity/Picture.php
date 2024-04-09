<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pictures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    #[Assert\Image]
    private ?File $file = null;

    #[ORM\Column(length: 2048)]
    #[Assert\NotBlank]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $description = null;

    private bool $saveFile = true;

    private bool $setAsMainPicture = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): static
    {
        $this->trick = $trick;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

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

    public function getSaveFile(): bool
    {
        return $this->saveFile;
    }

    public function setSaveFile(bool $saveFile): static
    {
        $this->saveFile = $saveFile;

        return $this;
    }

    public function getSetAsMainPicture(): bool
    {
        return $this->setAsMainPicture;
    }

    public function setSetAsMainPicture(bool $setAsMainPicture): static
    {
        $this->setAsMainPicture = $setAsMainPicture;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFilename() ?? "no_filename";
    }
}
