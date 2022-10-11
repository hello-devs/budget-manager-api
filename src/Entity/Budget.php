<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BudgetRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'BUDGET_VIEW\', object)'),
        new Put(security: 'is_granted(\'BUDGET_UPDATE\', object)'),
        new Delete(security: 'is_granted(\'BUDGET_DELETE\', object)'),
        new Post(security: 'is_granted(\'ROLE_USER\')'),
        new GetCollection(security: 'is_granted(\'ROLE_ADMIN\')')]
)]
#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;
    /** @var Collection<int, BudgetTransaction> */
    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: BudgetTransaction::class, orphanRemoval: true)]
    private Collection $budgetTransaction;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        #[Groups(["user-info"])]
        private string             $name,
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'budget')]
        #[ORM\JoinColumn(nullable: false)]
        private User               $creator,
        #[ORM\Column(type: 'date_immutable')]
        #[Groups(["user-info"])]
        private DateTimeImmutable  $startDate,
        #[ORM\Column(type: 'date_immutable', nullable: true)]
        private ?DateTimeImmutable $endDate = null,
        #[ORM\Column(type: 'integer')]
        private int                $startAmount = 0
    ) {
        $this->budgetTransaction = new ArrayCollection();
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

    /**
     * @return Collection<int, BudgetTransaction>
     */
    public function getBudgetTransaction(): Collection
    {
        return $this->budgetTransaction;
    }
}
