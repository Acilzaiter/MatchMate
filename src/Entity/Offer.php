<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\OfferRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name;

    #[ORM\Column]
    #[Assert\Positive]
    private ?float $price;

    #[ORM\Column(length: 255)]
    private ?string $text;

    #[ORM\Column(name:"nbrClub")]
    #[Assert\Positive]
    private ?int $nbrclub;

    #[ORM\OneToMany(mappedBy: 'idoffer', targetEntity: Offer::class)]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getNbrclub(): ?int
    {
        return $this->nbrclub;
    }

    public function setNbrclub(int $nbrclub): static
    {
        $this->nbrclub = $nbrclub;

        return $this;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }


    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setIdoffer($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getIdoffer() === $this) {
                $subscription->setIdoffer(null);
            }
        }

        return $this;
    }


}
