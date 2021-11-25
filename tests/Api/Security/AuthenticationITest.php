<?php

namespace Tests\Api\Security;

use App\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class AuthenticationITest extends WebTestCase
{
    private ?object $hasher;

    /** @test
     * @throws Exception
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
        if (!$this->hasher instanceof UserPasswordHasher) {
            throw new Exception("hasher must be a instance of UserPasswordHasher class");
        }

        //We have a user in the database
        $user = new User();
        $pwd = $this->hasher->hashPassword($user, 'pwd');


        $user
            ->setUsername('tester')
            ->setEmail('tester@email.com')
            ->setPassword($pwd);



        //When the user request the api login endpoint
//        $client = static::createClient();
//        $crawler = $client->request('GET', '/api/login');
//
        //We expect
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->hasher = $container->get('security.user_password_hasher');
    }
}
