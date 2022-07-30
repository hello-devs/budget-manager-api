<?php

namespace Tests\units\Entity;

use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test_we_can_instantiate_transaction(): void
    {
        //Given
        $user = new User();

        //When
        $transaction = new Transaction(creator: $user);
        $transactionId = $transaction->getId();
        $creator = $transaction->getCreator();
        $transactionAmount = $transaction->getAmount();

        //Then
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNull($transactionId);
        $this->assertSame($user, $creator);
        $this->assertEquals(0, $transactionAmount);
    }
}
