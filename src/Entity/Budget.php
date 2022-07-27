<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BudgetRepository;
use App\Security\Voter\BudgetVoter;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    collectionOperations: [
        "post" => [
            "security" => "is_granted('ROLE_USER')"
        ],
        "get" => [
            "security" => "is_granted('ROLE_ADMIN')"
        ]
    ],
    itemOperations: [
        'get' => [
            "security" => "is_granted('" . BudgetVoter::VIEW . "', object)"
        ],
        'put' => [
            "security" => "is_granted('" . BudgetVoter::UPDATE . "', object)"
        ],
        'delete' => [
            "security" => "is_granted('" . BudgetVoter::DELETE . "', object)"
        ]
    ]
)]
#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string             $name,
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'budget')]
        #[ORM\JoinColumn(nullable: false)]
        private User               $creator,
        #[ORM\Column(type: 'date_immutable')]
        private DateTimeImmutable  $startDate,
        #[ORM\Column(type: 'date_immutable', nullable: true)]
        private ?DateTimeImmutable $endDate = null,
        #[ORM\Column(type: 'integer')]
        private int                $startAmount = 0,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStartAmount(): int
    {
        return $this->startAmount;
    }

    public function setStartAmount(int $startAmount): self
    {
        $this->startAmount = $startAmount;

        return $this;
    }
}
