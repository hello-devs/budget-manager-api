<?php

namespace Tests\units\Entity;

use App\Entity\Budget;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BudgetTest extends TestCase
{
    /** @test */
    public function we_can_instantiate_a_budget(): void
    {
        //  Given
        $budgetName = "New Budget";
        $user = new User();
        /** @var DateTimeImmutable $startDate */
        $startDate = date_create_immutable("2022-05-01");

        //  When
        $budget = new Budget(name: $budgetName, creator: $user, startDate: $startDate);

        //  Then
        $this->assertInstanceOf(Budget::class, $budget);
        $this->assertSame($user, $budget->getCreator());
        $this->assertSame($budgetName, $budget->getName());
        $this->assertSame($startDate, $budget->getStartDate());
        $this->assertNull($budget->getEndDate());
    }
}
