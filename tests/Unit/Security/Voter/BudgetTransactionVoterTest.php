<?php

namespace Tests\Unit\Security\Voter;

use App\Entity\Budget;
use App\Entity\BudgetTransaction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Security\Voter\BudgetTransactionVoter;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BudgetTransactionVoterTest extends TestCase
{
    /**
     * @dataProvider provideUserBudgetAndVote
     */
    public function test_that_voter_return_correct_vote_in_different_cases(
        UserInterface     $user,
        BudgetTransaction $budgetTransaction,
        bool              $expectedSecurityReturn,
        string            $attributeToVoteOn,
        int               $expectedVote
    ): void {
        //  Given
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('ROLE_ADMIN')->willReturn($expectedSecurityReturn);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        //  When
        $budgetTransactionVoter = new BudgetTransactionVoter($security);
        $vote = $budgetTransactionVoter->vote($token, $budgetTransaction, [$attributeToVoteOn]);

        //  Then
        $this->assertInstanceOf(Voter::class, $budgetTransactionVoter);
        $this->assertEquals($expectedVote, $vote);
    }

    public function provideUserBudgetAndVote(): Generator
    {
        $user1 = new User();
        $user2 = new User();

        //VIEW
        yield 'user who create the budget can access' => [
            $user1,
            new BudgetTransaction(
                budget: new Budget(name: "unit-test-budgetTransaction", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
                transaction: new Transaction($user1),
                impactDate: date_create_immutable("2022-01-01")
            ),
            false,
            BudgetTransactionVoter::VIEW,
            1
        ];

        yield 'user who don\'t create the budget cannot access' => [
            $user1,
            new BudgetTransaction(
                budget: new Budget(name: "unit-test-budgetTransaction", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
                transaction: new Transaction($user2),
                impactDate: date_create_immutable("2022-01-01")
            ),
            false,
            BudgetTransactionVoter::VIEW,
            -1
        ];

        yield 'admin can access the budget even if he isn\'t the creator' => [
            $user1,
            new BudgetTransaction(
                budget: new Budget(name: "unit-test-budgetTransaction", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
                transaction: new Transaction($user2),
                impactDate: date_create_immutable("2022-01-01")
            ),
            true,
            BudgetTransactionVoter::VIEW,
            1
        ];
    }
}
