<?php

namespace Tests\fonctionnals\UserManagement;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Tests\fonctionnals\AbstractApiTestCase;

class AdminUserManagementITest extends AbstractApiTestCase
{
    /**
     * @test
     * @dataProvider provideUserWithDifferentRoles
     * @param string $email
     * @param string $password
     * @param array<string> $role
     * @param int $expectedStatusCodeForListingUser
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function role_api_admin_is_required_to_manage_user(
        string $email,
        string $password,
        array  $role,
        int    $expectedStatusCodeForListingUser,
        int    $expectedStatusCodeForCreatingUser): void
    {
        //We have a user with basic "ROLE_USER"
        $this->createUserInDatabase($email, $password, $role);
        $token = $this->getToken("/get_token", ["email" => $email, "password" => $password]);

        //When we request users list
        $this->requestWithJwt("GET", "/api/users", $token);

        //We expect resource is accessible with at least ROLE_ADMIN
        $this->assertResponseStatusCodeSame($expectedStatusCodeForListingUser);

        //When we request user creation
        $this->requestWithJwt("POST", "/api/users", $token, [
            "email" => "new-user@email.com",
            "password" => "new-user-password"
        ]);

        //We expect user creation is accessible  with at least ROLE_CLIENT
//        dump($res->getStatusCode());
        $this->assertResponseStatusCodeSame($expectedStatusCodeForCreatingUser);

    }

    /**
     * @return array<mixed[]>
     */
    public function provideUserWithDifferentRoles(): array
    {
        return [
            ["tester@email.com", "password", ["ROLE_USER"], 403, 403],
            ["tester@email.com", "password", ["ROLE_CLIENT"], 403, 201],
            ["tester@email.com", "password", ["ROLE_ADMIN"], 200, 201],
            ["tester@email.com", "password", ["ROLE_SUPER_ADMIN"], 200, 201]
        ];
    }
}
