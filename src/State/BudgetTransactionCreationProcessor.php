<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\BudgetTransaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetTransactionCreationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator
    ) {
    }

    /**
     * @param BudgetTransaction $data
     * @param Operation $operation
     * @param mixed[] $uriVariables
     * @param mixed[] $context
     * @return BudgetTransaction
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): BudgetTransaction
    {
        if (!($data instanceof BudgetTransaction)) {
            //todo log error info for devs
            throw new HttpException(500);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $transaction = $data->getTransaction();
        $transaction->setCreator($user);

        $errors = $this->validator->validate($transaction);
        if (count($errors) > 0) {
            //todo log error info for devs
            throw new HttpException(500);
        }

        $this->entityManager->persist($data);

        return $data;
    }
}
