<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Doctrine\Migrations\Version\State;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type:"date")]
    private ?DateTimeInterface $date;

    #[ORM\Column(name:"startTime",type:"time")]
    private ?DateTimeInterface $starttime;

    #[ORM\Column(name:"endTime",type:"time")]
    private ?DateTimeInterface $endtime;

    #[ORM\Column(length: 255)]
    private ?string $type;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "idPlayer", referencedColumnName: "id")]
    private ?User $idplayer;

    #[ORM\ManyToOne(targetEntity: Stadium::class, inversedBy:'reservations', cascade: ["remove"])]
    #[ORM\JoinColumn(name: 'refStadium', referencedColumnName: 'reference')]
    private ?Stadium $refstadium;

    #[ORM\OneToMany(mappedBy: 'idReservation', targetEntity: Feedback::class, cascade: ['remove'])]
    private Collection $feedbacks;

    #[ORM\ManyToMany(targetEntity: Payment::class, inversedBy: 'idreservation')]
    #[ORM\JoinTable(name:"paymentreservation")]
    #[ORM\JoinColumn(name:"idreservation", referencedColumnName:"id")]
    #[ORM\InverseJoinColumn(name:"idPayment", referencedColumnName:"id")]
    private Collection $idpayment;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idpayment = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStarttime(): ?\DateTimeInterface
    {
        return $this->starttime;
    }

    public function setStarttime(\DateTimeInterface $starttime): static
    {
        $this->starttime = $starttime;

        return $this;
    }

    public function getEndtime(): ?\DateTimeInterface
    {
        return $this->endtime;
    }

    public function setEndtime(\DateTimeInterface $endtime): static
    {
        $this->endtime = $endtime;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getIdplayer(): ?User
    {
        return $this->idplayer;
    }

    public function setIdplayer(?User $idplayer): static
    {
        $this->idplayer = $idplayer;

        return $this;
    }

    public function getRefstadium(): ?Stadium
    {
        return $this->refstadium;
    }

    public function setRefstadium(?Stadium $refstadium): static
    {
        $this->refstadium = $refstadium;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getIdpayment(): Collection
    {
        return $this->idpayment;
    }

    public function addIdpayment(Payment $idpayment): static
    {
        if (!$this->idpayment->contains($idpayment)) {
            $this->idpayment->add($idpayment);
        }

        return $this;
    }

    public function removeIdpayment(Payment $idpayment): static
    {
        $this->idpayment->removeElement($idpayment);

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setIdReservation($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getIdReservation() === $this) {
                $feedback->setIdReservation(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        // Example of string representation: "Reservation for [Date] at [StartTime] - [EndTime]"
        return sprintf(
            'Reservation for %s at %s - %s',
            $this->date->format('Y-m-d'),
            $this->starttime->format('H:i'),
            $this->endtime->format('H:i')
        );
    }
}
