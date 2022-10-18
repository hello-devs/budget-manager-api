<?php

namespace Tests\units\Entity;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test_we_can_instantiate_transaction(): void
    {
        //Given
        $user = new User();

        //When
        $transaction = new Transaction();
        $transaction
            ->setCreator($user)
            ->setAmount(500);

        $transactionId = $transaction->getId();
        $creator = $transaction->getCreator();
        $transactionAmount = $transaction->getAmount();
        $transactionBudgetTransactions = $transaction->getBudgetTransaction();

        //Then
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNull($transactionId);
        $this->assertSame($user, $creator);
        $this->assertEquals(500, $transactionAmount);
        $this->assertInstanceOf(Collection::class, $transactionBudgetTransactions);
        $this->assertEmpty($transactionBudgetTransactions);
    }
}
