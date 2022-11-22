<?php

namespace Tests\Functional;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
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
     * @throws DecodingExceptionInterface
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
        //We have a user in the database
        $email = 'tester@email.com';
        $plainPassword = 'pwd';

        $this->createUserInDatabase($email, $plainPassword);


        //When request the api login endpoint with correct credentials
        $body = [
            'email' => $email,
            'password' => $plainPassword
        ];

        $response = $this->client->request(
            'POST',
            'get_token',
            [
                'json' => $body
            ]
        );


        //We expect the endpoint is accessible
        $this->assertResponseIsSuccessful();
        //We expect response  give us a json web tokens
        $this->assertArrayHasKey("token", $response->toArray());
        $this->assertArrayHasKey("refresh_token", $response->toArray());
    }
}
