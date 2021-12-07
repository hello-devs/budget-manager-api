<?php

namespace Tests\Api\UserManagement;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Tests\Api\AbstractApiTestCase;

class AdminUserManagementITest extends AbstractApiTestCase
{
    /**
     * @test
     * @dataProvider provideUserWithDifferentRoles
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function role_api_admin_is_required_to_manage_user(string $email, string $password, string $role, int $expectedStatusCode): void
    {
        //We have a user with basic "ROLE_USER"
        $this->createUserInDatabase($email, $password);
        $token = $this->getToken("/get_token", ["email" => $email, "password" => $password]);

        //When we request users list
        $this->requestWithJwt("GET", "/api/users", $token);

        //We expect resource is not accessible
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @return array<mixed[]>
     */
    public function provideUserWithDifferentRoles(): array
    {
        return[
            ["tester@email.com","password","ROLE_USER",403],
            ["tester@email.com","password","ROLE_CLIENT",403],
            ["tester@email.com","password","ROLE_ADMIN",200],
            ["tester@email.com","password","ROLE_SUPER_ADMIN",200]
        ];
    }
}
