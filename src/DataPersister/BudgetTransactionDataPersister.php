<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\BudgetTransaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetTransactionDataPersister implements DataPersisterInterface
{
    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator,
    ) {
    }


    public function supports($data): bool
    {
        return $data instanceof BudgetTransaction;
    }

    public function persist($data): void
    {
        /** @var User $creator */
        $creator = $this->security->getUser();
        /** @var BudgetTransaction $data */
        $transaction = $data->getTransaction();
        $transaction->setCreator($creator);

        if (count($this->validator->validate($transaction)) > 0) {
            throw new HttpException(500);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function remove($data): void
    {
        /** @var BudgetTransaction $data */
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
