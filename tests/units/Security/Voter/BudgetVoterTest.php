<?php

namespace Tests\units\Security\Voter;

use App\Entity\Budget;
use App\Entity\User;
use App\Security\Voter\BudgetVoter;
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
    public function test_that_we_can_instantiate_budget_voter_class(
        UserInterface $user,
        Budget        $budget,
        bool          $expectedSecurityReturn,
        int           $expectedVote
    ): void
    {
        //  Given
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('ROLE_ADMIN')->willReturn($expectedSecurityReturn);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        //  When
        $budgetVoter = new BudgetVoter($security);
        $vote = $budgetVoter->vote($token, $budget, [BudgetVoter::VIEW]);

        //  Then
        $this->assertInstanceOf(Voter::class, $budgetVoter);
        $this->assertEquals($expectedVote, $vote);
    }

    public function provideUserBudgetAndVote(): \Generator
    {
        $user1 = new User();
        $user2 = new User();

        yield 'user can access' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user1, startDate: new \DateTimeImmutable("2022-01-01")),
            false,
            1
        ];

        yield 'user cannot access' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new \DateTimeImmutable("2022-01-01")),
            false,
            -1
        ];

        yield 'user 1 as admin can' => [
            $user1,
            new Budget(name: "unit-test-budget", creator: $user2, startDate: new \DateTimeImmutable("2022-01-01")),
            true,
            1
        ];
    }
}
