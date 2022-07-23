<?php

namespace App\Security\Voter;

use App\Entity\Budget;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class BudgetVoter extends Voter
{

    const VIEW = "BUDGET_VIEW";

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            in_array($attribute, [self::VIEW]) &&
            $subject instanceof Budget;
    }

    /**
     * @param string $attribute
     * @param $subject Budget
     * @param TokenInterface $token
     * @return bool
     * @throws \Exception
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        switch ($attribute) {

            case self::VIEW:
                if ($subject->getCreator() === $user) {
                    return true;
                }
                return false;

            default:
                throw new \Exception(sprintf('Unhandled attribute "%s"', $attribute));
        }
    }

}