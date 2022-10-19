<?php

namespace App\Security\Voter;

use App\Entity\BudgetTransaction;
use App\Security\Voter\Enum\BudgetTransactionVoterCase;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class BudgetTransactionVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, BudgetTransactionVoterCase::values())
            && $subject instanceof BudgetTransaction;
    }

    /**
     * @param string $attribute
     * @param BudgetTransaction $subject
     * @param TokenInterface $token
     * @return bool
     * @throws Exception
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted("ROLE_ADMIN")) {
            return true;
        }

        $user = $token->getUser();
        $budgetCreator = $subject->getBudget()->getCreator();

        switch ($attribute) {
            case BudgetTransactionVoterCase::Create->value:
            case BudgetTransactionVoterCase::View->value:

                if ($budgetCreator === $user) {
                    return true;
                }
                return false;

            default:
                throw new Exception(sprintf('Unhandled attribute "%s"', $attribute));
        }
    }
}
