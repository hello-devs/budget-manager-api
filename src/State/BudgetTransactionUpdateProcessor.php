<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\BudgetTransactionUpdateDto;
use App\Entity\BudgetTransaction;
use App\Repository\BudgetTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BudgetTransactionUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BudgetTransactionRepository $budgetTransactionRepository,
        private readonly EntityManagerInterface      $entityManager,
    ) {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param mixed[] $uriVariables
     * @param mixed[] $context
     * @return BudgetTransaction
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BudgetTransaction
    {
        if (!$data instanceof BudgetTransactionUpdateDto) {
            throw new BadRequestException();
        }

        $budgetTransaction = $this->budgetTransactionRepository->find($uriVariables["id"]);

        if ($budgetTransaction === null) {
            throw new NotFoundHttpException();
        }

        $this->updateTransactionAmount($data, $budgetTransaction);

        $this->updateImpactDate($data, $budgetTransaction);

        $this->updateSign($data, $budgetTransaction);


        $this->entityManager->persist($budgetTransaction);
        $this->entityManager->flush();

        return $budgetTransaction;
    }

    /**
     * @param BudgetTransactionUpdateDto $data
     * @param BudgetTransaction $budgetTransaction
     * @return void
     */
    public function updateTransactionAmount(BudgetTransactionUpdateDto $data, BudgetTransaction $budgetTransaction): void
    {
        if ($data->transactionAmount !== null) {
            $budgetTransaction->getTransaction()->setAmount($data->transactionAmount);
        }
    }

    /**
     * @param BudgetTransactionUpdateDto $data
     * @param BudgetTransaction $budgetTransaction
     * @return void
     */
    public function updateImpactDate(BudgetTransactionUpdateDto $data, BudgetTransaction $budgetTransaction): void
    {
        if ($data->impactDate !== null) {
            $newImpactDate = date_create_immutable($data->impactDate);

            if ($newImpactDate === false) {
                throw new BadRequestException();
            }

            $budgetTransaction->setImpactDate($newImpactDate);
        }
    }

    /**
     * @param BudgetTransactionUpdateDto $data
     * @param BudgetTransaction $budgetTransaction
     * @return void
     */
    public function updateSign(BudgetTransactionUpdateDto $data, BudgetTransaction $budgetTransaction): void
    {
        if ($data->isNegative !== null) {
            if ($data->isNegative !== true) {
                $budgetTransaction->setNegative();
            } else {
                $budgetTransaction->setPositive();
            }
        }
    }
}
