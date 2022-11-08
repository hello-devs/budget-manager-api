<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
class BudgetTransactionUpdateDto
{
    #[Groups(["budget-transaction:write"])]
    #[Assert\Positive]
    public int|null $transactionAmount = null;

    #[Groups(["budget-transaction:write"])]
    public string|null $impactDate = null;

    #[Groups(["budget-transaction:write"])]
    public bool|null $isNegative = null;

    #[Groups(["budget-transaction:write"])]
    public bool|null $isRecurrent = null;
}
