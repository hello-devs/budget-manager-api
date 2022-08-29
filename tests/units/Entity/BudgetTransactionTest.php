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
    public function we_can_instantiate_BudgetTransaction_with_budget_and_transaction_object(): void
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

    /** @test */
    public function we_can_set_sign_to_negative(): void
    {
        //Given
        $creator = new User();
        $transaction = new Transaction($creator);
        $budget = new Budget("budget transaction test", $creator, date_create_immutable("2022-05-01"));

        //When
        $budgetTransaction = new BudgetTransaction(budget: $budget, transaction: $transaction);
        $methodReturn = $budgetTransaction->setNegative();

        //Then
        $this->assertTrue($budgetTransaction->isNegative());
        $this->assertEquals($budgetTransaction, $methodReturn);
    }

    /** @test */
    public function we_can_set_sign_to_positive(): void
    {
        //Given
        $creator = new User();
        $transaction = new Transaction($creator);
        $budget = new Budget("budget transaction test", $creator, date_create_immutable("2022-05-01"));

        //When
        $budgetTransaction = new BudgetTransaction(budget: $budget, transaction: $transaction, isNegative: true);
        $methodReturn = $budgetTransaction->setPositive();

        //Then
        $this->assertTrue($budgetTransaction->isPositive());
        $this->assertEquals($budgetTransaction, $methodReturn);
    }
}
