<?php

namespace Tests\functional;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthenticationITest extends AbstractApiTestCase
{
    /** @test
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
//        //We have a user in the database
        $email = 'tester@email.com';
        $plainPassword = 'pwd';

        $this->createUserInDatabase($email, $plainPassword);


        //When request the api login endpoint with correct credentials
        $body = [
            'email' => $email,
            'password' => $plainPassword
        ];

        $jwt = $this->getToken('get_token', $body);

        //We expect the endpoint is accessible
        $this->assertResponseIsSuccessful();
        //We expect response  give us a not null json web token
        $this->assertNotNull($jwt);
    }
}
