<?php

namespace App\Repository;

use App\Entity\BudgetTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BudgetTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetTransaction[]    findAll()
 * @method BudgetTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template-extends ServiceEntityRepository<BudgetTransaction>
 */
class BudgetTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetTransaction::class);
    }
}
