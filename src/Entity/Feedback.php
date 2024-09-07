<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClubRepository;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: "idReservation", referencedColumnName: "id")]
    private ?Reservation $idReservation;

    #[ORM\Column(name: "questionResponse1",type: "text", nullable: true)]
    private ?string $responseQuestion1;

    #[ORM\Column(name: "questionResponse2",type: "text", nullable: true)]
    private ?string $responseQuestion2 = null;

    #[ORM\Column(name: "questionResponse3",type: "text", nullable: true)]
    private ?string $responseQuestion3 = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "userId", referencedColumnName: "id")]
    private ?User $user;

    // Getter and Setter for User
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setResponseQuestion1(?string $responseQuestion1): self
    {
        $this->responseQuestion1 = $responseQuestion1;
        return $this;
    }

    public function getResponseQuestion1(): ?string
    {
        return $this->responseQuestion1;
    }

    public function setResponseQuestion2(?string $responseQuestion2): self
    {
        $this->responseQuestion2 = $responseQuestion2;
        return $this;
    }

    public function getResponseQuestion2(): ?string
    {
        return $this->responseQuestion2;
    }

    public function setResponseQuestion3(?string $responseQuestion3): self
    {
        $this->responseQuestion3 = $responseQuestion3;
        return $this;
    }

    public function getResponseQuestion3(): ?string
    {
        return $this->responseQuestion3;
    }

    public function getIdReservation(): ?Reservation
    {
        return $this->idReservation;
    }

    public function setIdReservation(?Reservation $idReservation): self
    {
        $this->idReservation = $idReservation;
        return $this;
    }
}
