<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BudgetTransactionRepository;
use App\Security\Voter\BudgetTransactionVoter;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    collectionOperations: [
        "post" => [
            "security_post_denormalize" => "is_granted('" . BudgetTransactionVoter::CREATE . "', object)"
        ]
    ],
    itemOperations: [
        "get" => [
            "security" => "is_granted('" . BudgetTransactionVoter::VIEW . "', object)"
        ]
    ]
)]
#[ORM\Entity(repositoryClass: BudgetTransactionRepository::class)]
class BudgetTransaction
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'budgetTransaction')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Budget      $budget,
        #[ORM\ManyToOne(targetEntity: Transaction::class, cascade: ["persist"], inversedBy: 'budgetTransaction')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Transaction $transaction,
        #[ORM\Column('date_immutable')]
        private DateTimeImmutable    $impactDate,
        #[ORM\Column(type: 'boolean')]
        private bool                 $isNegative = false,
        #[ORM\Column(type: 'boolean')]
        private bool                 $isRecurrent = false
    ) {
    }


    public function getId(): int|null
    {
        return $this->id;
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function isNegative(): bool
    {
        return $this->isNegative;
    }

    public function isPositive(): bool
    {
        return !$this->isNegative;
    }

    public function isRecurrent(): bool
    {
        return $this->isRecurrent;
    }

    public function setNegative(): self
    {
        $this->isNegative = true;

        return $this;
    }

    public function setPositive(): self
    {
        $this->isNegative = false;

        return $this;
    }

    public function setImpactDate(DateTimeImmutable $newImpactDate): self
    {
        $this->impactDate = $newImpactDate;

        return $this;
    }

    public function getImpactDate(): DateTimeImmutable
    {
        return $this->impactDate;
    }
}
