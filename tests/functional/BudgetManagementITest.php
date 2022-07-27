<?php

namespace Tests\functional;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

        //When request the created budget
        $request = $this->getTheCreatedBudget($createdBudgetId, $user1Token);
        $requestedBudgetId = $request->toArray()["id"];

        //Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($createdBudgetId, $requestedBudgetId);

        //When request budget update
        $budgetData["name"] = "budget-edited-name";
        $updateRequest = $this->requestWithJwt(
            method: "PUT",
            url: "/api/budgets/$createdBudgetId",
            token: $user1Token,
            json: $budgetData
        );

        $updateResponse = $updateRequest->toArray();
        $updatedName = $updateResponse["name"];
        $updatedBudgetId = $updateResponse["id"];

        //Then expect only name change
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame("budget-edited-name", $updatedName);
        $this->assertSame($requestedBudgetId, $updatedBudgetId);


        //When request to delete the budget
        $request = $this->requestWithJwt(
            "DELETE",
            "/api/budgets/$createdBudgetId",
            token: $user1Token
        );

        //Then expect successful deletion
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        //When
        $this->getTheCreatedBudget($createdBudgetId, $user1Token);

        //Then
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function getTheCreatedBudget(int $budgetID, string $userToken): ResponseInterface
    {
        return $this->requestWithJwt(
            "GET",
            "/api/budgets/$budgetID",
            token: $userToken
        );
    }
}
