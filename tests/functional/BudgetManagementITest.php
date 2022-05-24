<?php

namespace Tests\functional;

use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
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
     */
    public function a_user_can_create_a_budget_then_this_user_can_manage_his_budget(): void
    {
        //Given
        $apiClientEmail = "api-client@email";
        $apiClientPassword = "api-pwd";
        $this->createUserInDatabase($apiClientEmail, $apiClientPassword, ["ROLE_CLIENT"]);
        $this->getToken("/get_token", ["email" => $apiClientEmail, "password" => $apiClientPassword]);

        $user1Email = "user1@email.com";
        $user1Password = "user1-pwd";
        $user1Id = $this->createUserInDatabase($user1Email, $user1Password);

        $user1Token = $this->getToken("/get_token", ["email" => $user1Email, "password" => $user1Password]);

        $budgetData = [
            "name" => "budget-name",
            "startDate" => date("Y-m-01"),
            "startAmount" => 10000,
            "creator" => "/api/users/$user1Id"
        ];

        //When
        $this->requestWithJwt(
            "POST",
            "/api/budgets",
            $user1Token,
            $budgetData
        );

        //Then
        $this->assertResponseStatusCodeSame(201);
    }
}
