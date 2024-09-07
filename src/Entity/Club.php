<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ClubRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Timezone;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


#[ORM\Entity(repositoryClass:ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255, nullable:true)]
    #[Assert\NotBlank(message: 'Name cannot be blank')]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]*$/', message: 'Name cannot contain symbols or numbers')]
    private ?string $name=null;
    

    #[ORM\Column(length: 255, nullable:true)]
    #[Assert\NotBlank(message: 'Governorate cannot be blank')]
    private ?string $governorate=null;

    #[ORM\Column(length: 255, nullable:true)]
    #[Assert\NotBlank(message: 'City cannot be blank')]
    private ?string $city=null;

    #[ORM\Column(name: "startTime", type: "time")]
    #[Assert\LessThan(propertyPath: "endtime", message: "Start time must be before end time")]
    private ?DateTimeInterface $starttime;
    
    #[ORM\Column(name: "endTime", type: "time")]
    #[Assert\GreaterThan(propertyPath: "starttime", message: "End time must be after start time")]
    private ?DateTimeInterface $endtime;

    #[ORM\Column(name:"stadiumNbr")]
    private ?int $stadiumnbr;

    #[ORM\Column(length: 255,nullable:true)]
    #[Assert\NotBlank(message: 'Description cannot be blank')]
    private ?string $description=null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'longitude cannot be blank')]
    private ?float $longitude;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'latitude cannot be blank')]
    private ?float $latitude;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'clubs')]
    #[ORM\JoinColumn(name: "idUser", referencedColumnName: "id")]
    private ?User $iduser;

    #[ORM\ManyToMany(targetEntity: Image::class, inversedBy: 'idclub', cascade: ["remove"])]
    #[ORM\JoinTable(name:"imageclub")]
    #[ORM\JoinColumn(name:"idClub", referencedColumnName:"id")]
    #[ORM\InverseJoinColumn(name:"idImage", referencedColumnName:"id")]
    private Collection $idimage;

    #[ORM\OneToMany(mappedBy: 'idclub', targetEntity: Stadium::class, cascade: ["remove"])]
    private Collection $stadiums;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idimage = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stadiums = new ArrayCollection();
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

    public function getGovernorate(): ?string
    {
        return $this->governorate;
    }

    public function setGovernorate(?string $governorate): static
    {
        $this->governorate = $governorate;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStarttime(): ?\DateTimeInterface
    {
        return $this->starttime;
    }

    public function setStarttime(?\DateTimeInterface $starttime): static
    {
        $this->starttime = $starttime;

        return $this;
    }

    public function getEndtime(): ?\DateTimeInterface
    {
        return $this->endtime;
    }

    public function setEndtime(?\DateTimeInterface $endtime): static
    {
        $this->endtime = $endtime;

        return $this;
    }

    public function getStadiumnbr(): ?int
    {
        return $this->stadiumnbr;
    }

    public function setStadiumnbr(?int $stadiumnbr): static
    {
        $this->stadiumnbr = $stadiumnbr;

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

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getIduser(): ?User
    {
        return $this->iduser;
    }

    public function setIduser(?User $iduser): static
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getIdimage(): Collection
    {
        return $this->idimage;
    }

    public function addIdimage(Image $idimage): static
    {
        if (!$this->idimage->contains($idimage)) {
            $this->idimage->add($idimage);
        }

        return $this;
    }

    public function removeIdimage(Image $idimage): static
{
    $this->idimage->removeElement($idimage);

    return $this;
}




    /**
     * @return Collection<int, Event>
     */
    public function getStadiums(): Collection
    {
        return $this->stadiums;
    }

}
