<?php

namespace App\Security\Voter;

use App\Entity\Budget;
use App\Security\Voter\Enum\BudgetVoterCase;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class BudgetVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, BudgetVoterCase::values()) &&
            $subject instanceof Budget;
    }

    /**
     * @param string $attribute
     * @param Budget $subject
     * @param TokenInterface $token
     * @return bool
     * @throws Exception
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        switch ($attribute) {
            case BudgetVoterCase::View->value:
            case BudgetVoterCase::Update->value:
            case BudgetVoterCase::Delete->value:

                if ($subject->getCreator() === $user) {
                    return true;
                }
                return false;

            default:
                throw new Exception(sprintf('Unhandled attribute "%s"', $attribute));
        }
    }
}
