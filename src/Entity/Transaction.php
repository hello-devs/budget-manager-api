<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    /** @var Collection<int, BudgetTransaction> */
    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: BudgetTransaction::class, orphanRemoval: true)]
    private Collection $budgetTransaction;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'transaction')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        #[Assert\NotNull]
        private User|null $creator = null,
        #[ORM\Column(type: 'integer')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private int       $amount = 0,
        #[ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(type: 'integer')]
        #[Groups(["budget-transaction:read", "budget-transaction:write"])]
        private ?int $id = null
    ) {
        $this->budgetTransaction = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getCreator(): User|null
    {
        return $this->creator;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Transaction
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Collection<int, BudgetTransaction>
     */
    public function getBudgetTransaction(): Collection
    {
        return $this->budgetTransaction;
    }

    public function setCreator(User $user): self
    {
        $this->creator = $user;
        return $this;
    }
}
