<?php

namespace Test\Api\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationIT extends WebTestCase
{
    /** @test */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password()
    {
        //We have a user in the database
        $user = new \App\Entity\User();


        //When the user request the api login endpoint
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/login');

        $this->assertResponseIsSuccessful();
    }

}