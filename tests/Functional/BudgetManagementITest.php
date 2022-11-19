<?php

namespace Tests\Functional;

use App\Entity\Budget;
use App\Entity\BudgetTransaction;
use App\Entity\Transaction;
use App\Entity\User;
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
        $adminEmail = "admin@email.dev";
        $adminPassword = "admin-pwd";
        $this->createUserInDatabase($adminEmail, $adminPassword, ["ROLE_ADMIN"]);
        $adminToken =  $this->getToken("/get_token", ["email" => $adminEmail, "password" => $adminPassword]);

        $user1Email = "user1@email.com";
        $user1Password = "user1-pwd";
        $budgetTransactionAmount = 500;
        $budgetTransactionImpactDate = "20220501";
        $budgetTransactionUpdatedImpactDate = "20220502";
        $budgetTransactionUpdatedAmount = 100;

        /**
         * When we request user creation
         * with admin token
         */
        $user = $this->checkWeCanCreateUserWithAdminRole($adminToken, $user1Email, $user1Password);


        /** @var int $userId */
        $userId = $user->getId();
        $user1Token = $this->getToken("/get_token", ["email" => $user1Email, "password" => $user1Password]);

        $budgetData = [
            "name" => "budget-name",
            "startDate" => date("Y-m-01"),
            "startAmount" => 10_000,
            "creator" => "/api/users/$userId}"
        ];



        /**
         * When we request to create a budget
         * with a valid user token
         */
        $createdBudget = $this->checkValidUserCanCreateBudget($user1Token, $budgetData);


        /**
         * When we request the created budget
         * with the token of the creator
         * and with the id of the budget
         */
        $this->checkWeCanRetrieveCreatedBudgetWithId($createdBudget, $user1Token);


        /**
         * When we request budget update
         * changing the name of the budget
         * with PUT method
         * and putting all budget in json
         */
        $this->checkThatWeCanUpdateBudgetAndRetrieveUpdatedData($budgetData, $createdBudget, $user1Token);

        /**
         * When we request BudgetTransaction creation
         */
        $createdBudgetTransaction = $this->checkWeCanCreateBudgetTransaction(
            $user1Token,
            $createdBudget,
            $budgetTransactionAmount,
            $budgetTransactionImpactDate
        );

        /**
         * When we request BudgetTransaction update
         */
        $this->checkWeCanUpdateBudgetTransactionImpactDateAndAmount(
            $createdBudgetTransaction,
            $user1Token,
            $budgetTransactionUpdatedAmount,
            $budgetTransactionUpdatedImpactDate
        );

        /**
         * When we request BudgetTransaction deletion
         */
        $this->checkWeCanDeleteBudgetTransactionAndTransactionIsDeletedTwo($createdBudgetTransaction, $user1Token);


        /**
         * When request to delete the budget
         */
        $this->checkThatBudgetCanBeDelete($createdBudget, $user1Token);
    }

    /**
     * @param string $adminToken
     * @param string $user1Email
     * @param string $user1Password
     * @return User
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanCreateUserWithAdminRole(string $adminToken, string $user1Email, string $user1Password): User
    {
        $userCreationRequest = $this->requestWithJwt(
            "POST",
            "/api/users",
            $adminToken,
            [
                "email" => $user1Email,
                "password" => $user1Password
            ]
        );

        /** @var User $user */
        $user = $this->serializer->deserialize($userCreationRequest->getContent(), User::class, "json");

        $this->assertResponseStatusCodeSame(201);
        $this->assertInstanceOf(User::class, $user);
        return $user;
    }

    /**
     * @param string $user1Token
     * @param array<string, mixed> $budgetData
     * @return Budget
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkValidUserCanCreateBudget(string $user1Token, array $budgetData): Budget
    {
        $postResponse = $this->requestWithJwt(
            "POST",
            "/api/budgets",
            $user1Token,
            $budgetData
        );

        /** @var Budget $createdBudget */
        $createdBudget = $this->serializer->deserialize(
            $postResponse->getContent(),
            Budget::class,
            'json',
            ["groups" => "budget:read"]
        );

        //then
        $this->assertResponseStatusCodeSame(201);
        return $createdBudget;
    }

    /**
     * @param Budget $createdBudget
     * @param string $user1Token
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanRetrieveCreatedBudgetWithId(Budget $createdBudget, string $user1Token): void
    {
        /** @var int $createdBudgetId */
        $createdBudgetId = $createdBudget->getId();

        $request = $this->getTheCreatedBudget($createdBudgetId, $user1Token);
        $requestedBudgetId = $request->toArray()["id"];

        //Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($createdBudgetId, $requestedBudgetId);
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
     * @param string $user1Token
     * @param Budget $createdBudget
     * @param int $budgetTransactionAmount
     * @param string $budgetTransactionImpactDate
     * @return BudgetTransaction
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanCreateBudgetTransaction(
        string $user1Token,
        Budget $createdBudget,
        int    $budgetTransactionAmount,
        string $budgetTransactionImpactDate
    ): BudgetTransaction {
        /** @var int $createdBudgetId */
        $createdBudgetId = $createdBudget->getId();

        $budgetTransactionData = [
            "budget" => "/api/budgets/" . $createdBudgetId,
            "transaction" => [
                "amount" => $budgetTransactionAmount
            ],
            "impactDate" => $budgetTransactionImpactDate
        ];

        $budgetTransactionRequest = $this->requestWithJwt(
            method: "POST",
            url: "/api/budget_transactions",
            token: $user1Token,
            json: $budgetTransactionData
        );

        //Then
        $this->assertResponseStatusCodeSame(201);

        /** @var BudgetTransaction $budgetTransaction * */
        $budgetTransaction = $this->serializer->deserialize($budgetTransactionRequest->getContent(), BudgetTransaction::class, 'json');

        return $budgetTransaction;
    }

    /**
     * @param array<string, mixed> $budgetData
     * @param Budget $createdBudget
     * @param string $user1Token
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkThatWeCanUpdateBudgetAndRetrieveUpdatedData(
        array  $budgetData,
        Budget $createdBudget,
        string $user1Token
    ): void {
        /** @var int $createdBudgetId */
        $createdBudgetId = $createdBudget->getId();
        $budgetData["name"] = "budget-edited-name";

        $updateRequest = $this->requestWithJwt(
            method: "PUT",
            url: "/api/budgets/$createdBudgetId",
            token: $user1Token,
            json: $budgetData
        );

        $updateResponse = $updateRequest->toArray();
        $updatedName = $updateResponse["name"];

        //Then expect only name change
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame("budget-edited-name", $updatedName);
        $this->assertSame($budgetData['startAmount'], $updateResponse['startAmount']);
    }

    /**
     * @param BudgetTransaction $createdBudgetTransaction
     * @param string $user1Token
     * @param int $budgetTransactionUpdatedAmount
     * @param string $budgetTransactionUpdatedImpactDate
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkWeCanUpdateBudgetTransactionImpactDateAndAmount(
        BudgetTransaction $createdBudgetTransaction,
        string            $user1Token,
        int               $budgetTransactionUpdatedAmount,
        string            $budgetTransactionUpdatedImpactDate
    ): void {
        $requestBTUpdate = $this->requestWithJwt(
            "PUT",
            "/api/budget_transactions/{$createdBudgetTransaction->getId()}",
            $user1Token,
            [
                "transactionAmount" => $budgetTransactionUpdatedAmount,
                "impactDate" => $budgetTransactionUpdatedImpactDate
            ]
        );

        $updatedTransactionData = $requestBTUpdate->toArray();

        $oldBudgetTransactionImpactDate = date_create_immutable($budgetTransactionUpdatedImpactDate);
        $updatedBudgetTransactionImpactDate = date_create_immutable($updatedTransactionData["impactDate"]);

        $this->assertEquals($oldBudgetTransactionImpactDate, $updatedBudgetTransactionImpactDate);
        $this->assertEquals($budgetTransactionUpdatedAmount, $updatedTransactionData["transaction"]["amount"]);
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @param BudgetTransaction $createdBudgetTransaction
     * @param string $user1Token
     * @return void
     * @throws TransportExceptionInterface
     */
    public function checkWeCanDeleteBudgetTransactionAndTransactionIsDeletedTwo(BudgetTransaction $createdBudgetTransaction, string $user1Token): void
    {
        $this->requestWithJwt("DELETE", "/api/budget_transactions/{$createdBudgetTransaction->getId()}", $user1Token);
        $this->assertResponseStatusCodeSame(204);

        $this->requestWithJwt("GET", "/api/budget_transactions/{$createdBudgetTransaction->getId()}", $user1Token);
        $this->assertResponseStatusCodeSame(404);

        $isTransactionDelete = null === $this->entityManager->find(Transaction::class, $createdBudgetTransaction->getTransaction()->getId());
        $this->assertTrue($isTransactionDelete);
    }

    /**
     * @param Budget $createdBudget
     * @param string $user1Token
     * @return void
     * @throws TransportExceptionInterface
     */
    public function checkThatBudgetCanBeDelete(Budget $createdBudget, string $user1Token): void
    {
        /** @var int $createdBudgetId */
        $createdBudgetId = $createdBudget->getId();

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
}
