<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\BudgetTransactionRepository;
use App\Security\Voter\BudgetTransactionVoter;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => 'budget-transaction:read'],
            security: 'is_granted("' . BudgetTransactionVoter::VIEW . '", object)'
        ),
        new Post(
            normalizationContext: ['groups' => 'budget-transaction:read'],
            denormalizationContext: ['groups' => 'budget-transaction:write'],
            securityPostDenormalize: 'is_granted("' . BudgetTransactionVoter::CREATE . '", object)'
        )
    ]
)]
#[ORM\Entity(repositoryClass: BudgetTransactionRepository::class)]
class BudgetTransaction
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(["budget-transaction:read", "budget-transaction:write"])]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'budgetTransaction')]
        #[ORM\JoinColumn(nullable: false)]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private readonly Budget      $budget,
        #[ORM\ManyToOne(targetEntity: Transaction::class, cascade: ["persist"], inversedBy: 'budgetTransaction')]
        #[ORM\JoinColumn(nullable: false)]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private readonly Transaction $transaction,
        #[ORM\Column('date_immutable')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private DateTimeImmutable    $impactDate,
        #[ORM\Column(type: 'boolean')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private bool                 $isNegative = false,
        #[ORM\Column(type: 'boolean')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
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
