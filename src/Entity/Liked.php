<?php

namespace App\Entity;

use App\Repository\LikedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass:LikedRepository::class)]
#[ORM\Table(name: "Likeds")]
class Liked
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'refstadium', cascade:["persist","remove"])]
    #[ORM\JoinColumn(name: "idUser", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Stadium::class, inversedBy: 'iduser', cascade:["persist","remove"])]
    #[ORM\JoinColumn(name: "refStadium", referencedColumnName: "reference", onDelete: "CASCADE")]
    private ?Stadium $stadium;

    
    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $likedAt;

    public function __construct()
    {
        $this->likedAt = new \DateTime();
    }

    public function getLikedAt(): \DateTimeInterface
    {
        return $this->likedAt;
    }
  


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStadium(): ?Stadium
    {
        return $this->stadium;
    }

    public function setStadium(?Stadium $stadium): self
    {
        $this->stadium = $stadium;
        return $this;
    }
}
