<?php

namespace Tests\Functional;

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
        $user1Token = $this->getToken("/get_token", ["email" => $user1Email, "password" => $user1Password]);
        $budgetData = [
            "name" => "budget-name",
            "startDate" => date("Y-m-01"),
            "startAmount" => 10_000,
            "creator" => "/api/users/$user1Id"
        ];
        $budgetTransactionAmount = 500;
        $budgetTransactionImpactDate = "20220501";
        $budgetTransactionUpdatedImpactDate = "20220502";
        $budgetTransactionUpdatedAmount = 100;


        /**
         * When we request to create a budget
         * with a valid user token
         */
        $createdBudgetId = $this->checkValidUserCanCreateBudget($user1Token, $budgetData);


        /**
         * When we request the created budget
         * with the token of the creator
         * and with the id of the budget
         */
        $this->checkWeCanRetrieveCreatedBudgetWithId($createdBudgetId, $user1Token);


        /**
         * When we request budget update
         * changing the name of the budget
         * with PUT method
         * and putting all budget in json
         */
        $this->checkThatWeCanUpdateBudgetAndRetrieveUpdatedData($budgetData, $createdBudgetId, $user1Token);

        /**
         * When we request BudgetTransaction creation
         */
        $budgetTransactionData = [
            "budget" => "/api/budgets/" . $createdBudgetId,
            "transaction" => [
                "amount" => $budgetTransactionAmount
            ],
            "impactDate" => $budgetTransactionImpactDate
        ];

        $createdBudgetTransactionData = $this->checkWeCanCreateBudgetTransaction($user1Token, $budgetTransactionData);

        /**
         * When we request BudgetTransaction update
         */

        $createdBudgetTransactionData["transaction"]["amount"] = $budgetTransactionUpdatedAmount;
        $createdBudgetTransactionData["impactDate"] = $budgetTransactionUpdatedImpactDate;

        $requestBTUpdate = $this->requestWithJwt(
            "PUT",
            "/api/budget_transactions/{$createdBudgetTransactionData['id']}",
            $user1Token,
            $createdBudgetTransactionData
        );

        $updatedTransactionData = $requestBTUpdate->toArray();

        $this->assertEquals(
            date_create_immutable($budgetTransactionUpdatedImpactDate),
            date_create_immutable($updatedTransactionData["impactDate"])
        );
        $this->assertEquals($budgetTransactionUpdatedAmount, $updatedTransactionData["transaction"]["amount"]);
        $this->assertResponseStatusCodeSame(200);

        /**
         * When we request BudgetTransaction deletion
         */


        /**
         * When request to delete the budget
         */
        $this->checkThatBudgetCanBeDelete($createdBudgetId, $user1Token);
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

    /**
     * @param int $createdBudgetId
     * @param string $user1Token
     * @return void
     * @throws TransportExceptionInterface
     */
    public function checkThatBudgetCanBeDelete(int $createdBudgetId, string $user1Token): void
    {
        $this->requestWithJwt(
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
     * @param string $user1Token
     * @param array<string, mixed> $budgetTransactionData
     * @return array{
     *          "id" : int,
     *          "budget" : string,
     *          "transaction" : array<string, string|int>,
     *          "impactDate" : string
     * }
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanCreateBudgetTransaction(string $user1Token, array $budgetTransactionData): array
    {
        $budgetTransactionRequest = $this->requestWithJwt(
            method: "POST",
            url: "/api/budget_transactions",
            token: $user1Token,
            json: $budgetTransactionData
        );

        /** @var array{
         *          "id" : int,
         *          "budget" : string,
         *          "transaction" : array<string, string|int>,
         *          "impactDate" : string
         * } $budgetTransactionData
         */
        $budgetTransactionData = $budgetTransactionRequest->toArray();

        //Then
        $this->assertResponseStatusCodeSame(201);

        return $budgetTransactionData;
    }

    /**
     * @param array<string, mixed> $budgetData
     * @param int $createdBudgetId
     * @param string $user1Token
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkThatWeCanUpdateBudgetAndRetrieveUpdatedData(array $budgetData, int $createdBudgetId, string $user1Token): void
    {
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
        $this->assertSame($budgetData['startAmount'], $updateResponse['startAmount']);
    }

    /**
     * @param int $createdBudgetId
     * @param string $user1Token
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanRetrieveCreatedBudgetWithId(int $createdBudgetId, string $user1Token): mixed
    {
        $request = $this->getTheCreatedBudget($createdBudgetId, $user1Token);
        $requestedBudgetId = $request->toArray()["id"];

        //Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($createdBudgetId, $requestedBudgetId);
        return $requestedBudgetId;
    }

    /**
     * @param string $user1Token
     * @param array<string, mixed> $budgetData
     * @return int
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkValidUserCanCreateBudget(string $user1Token, array $budgetData): int
    {
        $postResponse = $this->requestWithJwt(
            "POST",
            "/api/budgets",
            $user1Token,
            $budgetData
        );

        /** @var int $createdBudgetId */
        $createdBudgetId = $postResponse->toArray()["id"];

        //then
        $this->assertResponseStatusCodeSame(201);
        return $createdBudgetId;
    }
}
