<?php

namespace Tests\units\Entity;

use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test_we_can_instantiate_transaction(): void
    {
        //Given

        //When
        $transaction = new Transaction();
        $transactionId = $transaction->getId();
        $transactionAmount = $transaction->getAmount();

        //Then
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNull($transactionId);
        $this->assertEquals(0, $transactionAmount);
    }
}
