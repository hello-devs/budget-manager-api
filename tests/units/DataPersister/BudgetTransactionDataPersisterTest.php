<?php

namespace Tests\units\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\DataPersister\BudgetTransactionDataPersister;
use App\Entity\BudgetTransaction;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetTransactionDataPersisterTest extends TestCase
{
    public function test_that_instantiable_and_implement_data_transformer_interface(): void
    {
        //Given
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManager::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method("validate")->willReturn([]);

        $budgetTransactionDT = new BudgetTransactionDataPersister($security, $entityManager, $validator);

        //When

        //Then
        $this->assertInstanceOf(DataPersisterInterface::class, $budgetTransactionDT);
        $this->assertInstanceOf(BudgetTransactionDataPersister::class, $budgetTransactionDT);
    }

    public function test_that_throw_error_with_invalid_transaction(): void
    {
        //Given
        $security = $this->createMock(Security::class);
        $security->method("getUser")->willReturn(new User());

        $entityManager = $this->createMock(EntityManager::class);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method("validate")->willReturn(["Constraint violation"]);

        $budgetTransaction = $this->createMock(BudgetTransaction::class);


        $budgetTransactionDT = new BudgetTransactionDataPersister($security, $entityManager, $validator);

        //When
        $this->expectException(HttpException::class);
        $budgetTransactionDT->persist($budgetTransaction);
    }


    public function test_support_of_budget_transaction(): void
    {
        //Given
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManager::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $budgetTransactionDT = new BudgetTransactionDataPersister($security, $entityManager, $validator);
        $budgetTransaction = $this->createMock(BudgetTransaction::class);

        //When
        $isSupported = $budgetTransactionDT->supports($budgetTransaction);

        //Then
        $this->assertTrue($isSupported);
    }
}
