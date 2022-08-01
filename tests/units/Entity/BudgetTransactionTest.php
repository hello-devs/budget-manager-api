<?php

namespace Tests\units\Entity;

use App\Entity\Budget;
use App\Entity\BudgetTransaction;
use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BudgetTransactionTest extends TestCase
{
    /** @test */
    public function can_instantiate_BudgetTransaction(): void
    {
        //Given
        $creator = new User();
        $transaction = new Transaction($creator);
        $budget = new Budget("budget transaction test", $creator, date_create_immutable("2022-05-01"));

        //When
        $budgetTransaction = new BudgetTransaction(budget: $budget, transaction: $transaction);
        $transactionInClass = $budgetTransaction->getTransaction();
        $budgetInClass = $budgetTransaction->getBudget();

        //Then we expect
        $this->assertInstanceOf(BudgetTransaction::class, $budgetTransaction);
        $this->assertNull($budgetTransaction->getId());
        $this->assertFalse($budgetTransaction->isNegative());
        $this->assertTrue($budgetTransaction->isPositive());
        $this->assertFalse($budgetTransaction->isRecurrent());
        $this->assertEquals($transaction, $transactionInClass);
        $this->assertEquals($budget, $budgetInClass);
    }
}
