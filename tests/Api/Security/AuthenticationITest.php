<?php

namespace Tests\Api\Security;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationITest extends ApiTestCase
{
    private UserPasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;
    private Client $client;

    /** @test
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
        $email = 'tester@email.com';
        $plainPassword = 'pwd';
        //We have a user in the database
        $user = new User();
        $pwd = $this->hasher->hashPassword($user, $plainPassword);

        //todo refactor in a create user function
        $user
            ->setEmail($email)
            ->setPassword($pwd)
            ->setRoles(['ROLE_USER', 'ROLE_TESTER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        //When the user request the api login endpoint
        //todo refactor in a request token function
        $content = json_encode([
            'email' => $email,
            'password' => $plainPassword
        ]);

        $jwt = null;

        $response = $this->client->request(
            'POST',
            '/get_token',
            [
                'body' => $content,
                'headers' => [
                    "CONTENT_TYPE" => "application/json",
                    "HTTP_ACCEPT" => "application/json"
                ]
            ],
        );

        $responseContent = $response->getContent();

        if ($responseContent
            && is_array(json_decode($responseContent, true))
            && array_key_exists('token', json_decode($responseContent, true))) {
            $jwt = json_decode($responseContent, true)['token'];
        }

        //We expect the endpoint exist
        $this->assertResponseIsSuccessful();
        //We expect response body give us a json web token
        $this->assertNotNull($jwt);
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->client = static::createClient();

        $container = static::getContainer();
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get('security.user_password_hasher');
        $this->hasher = $hasher;

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $this->entityManager = $em;
        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
    }
}
