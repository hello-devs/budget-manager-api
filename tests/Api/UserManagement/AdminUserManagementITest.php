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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function role_api_admin_is_required_to_manage_user(): void
    {
        $email = "tester@email.com";
        $plainPwd = "password";
        //We have a user with basic "ROLE_USER"
        $this->createUserInDatabase($email, $plainPwd);
        $token = $this->getToken("/get_token", ["email" => $email, "password" => $plainPwd]);

        //When we request users list
        $this->requestWithJwt("GET", "/api/users", $token);

        //We expect resource is not accessible
        $this->assertResponseStatusCodeSame(403);
    }

    //todo assert that ROLE_ADMIN can access user list
}
