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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function role_api_admin_is_required_to_manage_user()
    {
        $email="tester@email.com";
        $plainPwd = "password";
        //We have a user with basic "ROLE_USER"
        $this->createUserInDatabase($email,$plainPwd);
        $token = $this->getToken("/get_token",["email"=>$email,"password"=>$plainPwd]);
//        $this->createClientWithJwtCredential("/get_token",[])->request("GET","/api/users");
//        $clientWithJWT = $this->createClientWithJwtCredential("/get_token",["email"=>$email,"password"=>$plainPwd]);
        //When we request users list
//        $response = $clientWithJWT->request("GET","/api/users");
        //We expect resource is not accessible
        $this->assertResponseStatusCodeSame(400);



    }
}
