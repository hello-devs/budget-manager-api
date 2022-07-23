<?php

namespace Tests\functional;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BudgetManagementITest extends AbstractApiTestCase
{
    /**
     * @test
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function a_user_can_create_a_budget_then_this_user_can_manage_his_budget(): void
    {
        //Given
        $user1Email = "user1@email.com";
        $user1Password = "user1-pwd";
        $user1Id = $this->createUserInDatabase($user1Email, $user1Password);
        $budgetData = [
            "name" => "budget-name",
            "startDate" => date("Y-m-01"),
            "startAmount" => 10000,
            "creator" => "/api/users/$user1Id"
        ];

        //When
        $user1Token = $this->getToken("/get_token", ["email" => $user1Email, "password" => $user1Password]);
        $postResponse = $this->requestWithJwt(
            "POST",
            "/api/budgets",
            $user1Token,
            $budgetData
        );

        $createdBudgetId = $postResponse->toArray()["id"];

        //then
        $this->assertResponseStatusCodeSame(201);

        //When
        $request = $this->requestWithJwt(
            "GET",
            "/api/budgets/$createdBudgetId",
            token: $user1Token
        );

        $requestedBudgetId = $request->toArray()["id"];

        //Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($createdBudgetId, $requestedBudgetId);
    }
}
