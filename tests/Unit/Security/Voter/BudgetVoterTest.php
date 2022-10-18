<?php

namespace Tests\Unit\Security\Voter;

use App\Entity\Budget;
use App\Entity\User;
use App\Security\Voter\BudgetVoter;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BudgetVoterTest extends TestCase
{
    /**
     * @dataProvider provideUserBudgetAndVote
     */
    public function test_that_voter_return_correct_vote_in_different_cases(
        UserInterface $user,
        Budget        $budget,
        bool          $expectedSecurityReturn,
        string        $attributeToVoteOn,
        int           $expectedVote
    ): void {
        //  Given
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('ROLE_ADMIN')->willReturn($expectedSecurityReturn);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        //  When
        $budgetVoter = new BudgetVoter($security);
        $vote = $budgetVoter->vote($token, $budget, [$attributeToVoteOn]);

        //  Then
        $this->assertInstanceOf(Voter::class, $budgetVoter);
        $this->assertEquals($expectedVote, $vote);
    }

    public function provideUserBudgetAndVote(): Generator
    {
        $user1 = new User();
        $user2 = new User();

        //VIEW
        yield 'user who create the budget can access' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::VIEW,
            1
        ];

        yield 'user who don\'t create the budget cannot access' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::VIEW,
            -1
        ];

        yield 'admin can access the budget even if he isn\'t the creator' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
            true,
            BudgetVoter::VIEW,
            1
        ];

        //DELETION
        yield 'user can delete is own budget' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::DELETE,
            1
        ];

        yield 'user cannot delete budget of other user' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::DELETE,
            -1
        ];

        yield 'user as admin can delete budgets' => [
            $user2,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
            true,
            BudgetVoter::DELETE,
            1
        ];

        //UPDATE
        yield 'user can update is own budget' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::UPDATE,
            1
        ];

        yield 'user cannot update budget of other user' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new DateTimeImmutable("2022-01-01")),
            false,
            BudgetVoter::UPDATE,
            -1
        ];

        yield 'user as admin can update budgets' => [
            $user2,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new DateTimeImmutable("2022-01-01")),
            true,
            BudgetVoter::UPDATE,
            1
        ];
    }
}
