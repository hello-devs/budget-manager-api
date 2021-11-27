<?php

namespace Tests\Api\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationITest extends WebTestCase
{
    private UserPasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;
    private $client;

    /** @test
     * @throws Exception
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
        $username = 'tester';
        $email = 'tester@email.com';
        //We have a user in the database
        $user = new User();
        $pwd = $this->hasher->hashPassword($user, 'pwd');

        $user
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($pwd);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        //When the user request the api login endpoint
        $content = json_encode([
            'username' => $username,
            'password' => $pwd
        ]);

//        $client = static::createClient();
        $crawler = $this->client->request(
            'POST',
            '/api/login',
            [], [], [],
            $content
        );

        var_dump($this->client->getResponse()->getStatusCode());

        //We expect
        $this->assertResponseIsSuccessful();
    }

    protected function setUp(): void
    {
        parent::setUp();
//        self::bootKernel();
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
