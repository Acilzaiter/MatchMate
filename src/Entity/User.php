<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass:UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]

class User implements UserInterface
{

    public function getUserIdentifier()
    {
        // Return the unique identifier for the user (e.g., email)
        return $this->email;
    }

    public function getRoles()
    {
        // Return an array of roles for the user
        return [$this->role];

    }


    public function getSalt()
    {
        // You can ignore this method if you're using bcrypt for password hashing
        return null;
    }

    public function getUsername()
    {
        // Return the unique identifier for the user (e.g., email)
        return $this->email;
    }

    public function eraseCredentials()
    {
        // Implement this method if you need to erase sensitive data after authentication
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 20,name:"firstName")]
    private ?string $firstname;

    #[ORM\Column(length: 20,name:"lastName")]
    private ?string $lastname;

    #[ORM\Column(length: 8,name:"phoneNumber")]
    #[Assert\Positive]
    private ?int $phonenumber;
    
    #[ORM\Column(name:"birthDate",type:"date")]
    private ?DateTimeInterface $birthdate;

    #[ORM\Column(length: 255)]
    private ?string $location;

    #[ORM\Column(length: 255)]
    private ?string $gender;

    #[ORM\Column(length: 20)]
    private ?string $email;

    #[ORM\Column(length: 255)]
    private ?string $password;

    #[ORM\Column(length: 255)]
    /**
     * @Assert\Choice({"FieldOwner", "Player", "Admin"})
     */
    private ?string $role;

    #[ORM\Column(length: 255)]
    private ?string $image;

    #[ORM\Column(name:"creationDate",type:"date")]
    private ?DateTimeInterface $creationdate;

    #[ORM\Column(length: 255)]
    /**
     * @Assert\Choice({"Active","Inactive","Banned","Pending"})
     */
    private ?string $status;

    
    

    #[ORM\OneToMany(mappedBy: 'iduser', targetEntity: Club::class)]
    private Collection $clubs;
    private Collection $activities;

    #[ORM\OneToMany(mappedBy: 'idplayer', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'iduser', targetEntity: Notification::class, cascade: ["remove"])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'iduser', targetEntity: Payment::class)]
    private Collection $payments;

    #[ORM\ManyToMany(targetEntity: Stadium::class, inversedBy: 'iduser', cascade: ["persist","remove"])]
    #[ORM\JoinTable(name:"liked")]
    #[ORM\JoinColumn(name:"idUser", referencedColumnName:"id", onDelete:"CASCADE")]
    #[ORM\InverseJoinColumn(name:"refStadium", referencedColumnName:"reference", onDelete:"CASCADE")]
    private Collection $refstadium;

    #[ORM\OneToMany(mappedBy: 'iduser', targetEntity: Subscription::class, cascade: ["remove"])]
    private Collection $subscriptions;


    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->refstadium = new ArrayCollection();
        $this->clubs = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhonenumber(): ?int
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(int $phonenumber): static
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @Assert\Choice({"FieldOwner", "Player", "Admin"})
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreationdate(): ?\DateTimeInterface
    {
        return $this->creationdate;
    }

    public function setCreationdate(\DateTimeInterface $creationdate): static
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

   

    /**
     * @return Collection<int, Stadium>
     */
    public function getRefstadium(): Collection
    {
        return $this->refstadium;
    }

    public function addRefstadium(Stadium $refstadium): static
    {
        if (!$this->refstadium->contains($refstadium)) {
            $this->refstadium->add($refstadium);
            $refstadium->addIduser($this);
        }

        return $this;
    }

    public function removeRefstadium(Stadium $refstadium): static
    {
        if ($this->refstadium->removeElement($refstadium)) {
            $refstadium->removeIduser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getClubs(): Collection
    {
        return $this->clubs;
    }

    public function addClub(Club $club): static
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs->add($club);
            $club->setIduser($this);
        }

        return $this;
    }

    public function removeClub(Club $club): static
    {
        if ($this->clubs->removeElement($club)) {
            // set the owning side to null (unless already changed)
            if ($club->getIduser() === $this) {
                $club->setIduser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setIdplayer($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getIdplayer() === $this) {
                $reservation->setIdplayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setIduser($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getIduser() === $this) {
                $payment->setIduser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setIduser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getIduser() === $this) {
                $notification->setIduser(null);
            }
        }

        return $this;
    }


    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }


    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setIduser($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        $this->subscriptions->removeElement($subscription);
        // set the owning side to null (unless already changed)
        if ($subscription->getIduser() === $this) {
            $subscription->setIduser(null);
        }

        return $this;
    }


}
