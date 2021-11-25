<?php

namespace Tests\Api\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationITest extends WebTestCase
{
    private UserPasswordHasherInterface $hasher;
    private EntityManagerInterface $entityManager;

    /** @test
     * @throws Exception
     */
    public function user_should_be_able_to_get_jwt_token_when_authenticated_with_correct_login_and_password(): void
    {
        //We have a user in the database
        $user = new User();
        $pwd = $this->hasher->hashPassword($user, 'pwd');

        $user
            ->setUsername('tester')
            ->setEmail('tester@email.com')
            ->setPassword($pwd)
        ;

        //Todo: map User class with doctrine to fix error
        $this->entityManager->persist($user);

        //When the user request the api login endpoint
//        $client = static::createClient();
//        $crawler = $client->request('GET', '/api/login');
//
        //We expect
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

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
        if($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
    }
}
