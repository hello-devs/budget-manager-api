<?php

namespace App\Entity;

use DateTimeImmutable;

class BudgetTransaction
{
    private ?int $id = null;

    public function __construct(
        private readonly Budget      $budget,
        private readonly Transaction $transaction,
        private DateTimeImmutable $impactDate,
        private bool                 $isNegative = false,
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
