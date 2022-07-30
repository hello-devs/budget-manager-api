<?php

namespace App\Entity;

class Transaction
{
    public function __construct(
        private readonly User $creator,
        private readonly ?int $id = null,
        private int $amount = 0
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getCreator(): User
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


}
