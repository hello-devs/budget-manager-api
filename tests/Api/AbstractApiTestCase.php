<?php

namespace Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class AbstractApiTestCase extends ApiTestCase
{
    protected Client $client;
    protected UserPasswordHasherInterface $hasher;
    protected EntityManagerInterface $entityManager;
    protected string $token;

    /**
     * @param string $email
     * @param string $password
     * @param string[] $roles
     */
    protected function createUserInDatabase(string $email, string $password, array $roles = ['ROLE_USER']): void
    {
        $user = new User();
        $hashedPassword = $this->hasher->hashPassword($user, $password);

        $user
            ->setEmail($email)
            ->setPassword($hashedPassword)
            ->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param array<string,string> $body
     *  must contain valid user data formatted as:
     *  [
     *      "email" or "username" => "value",
     *      "password" => "value"
     *  ]
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function createClientWithJwtCredential(string $authenticationUrl, array $body = [], ?string $token= null): Client
    {
        $token = $token ?: $this->getToken($authenticationUrl, $body);

        return static::createClient([], ['headers' => ['authorization' => 'Bearer '.$token]]);
    }

    /**
     * @param string $authenticationUrl
     * @param array<string,string> $body
     * @return string|null
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function getToken(string $authenticationUrl, array $body = []): ?string
    {
        if (isset($this->token)) {
            return $this->token;
        }

        $body = json_encode($body);

        $response = $this->client->request(
            'POST',
            $authenticationUrl,
            ['body' => $body]
        );

        /** @var array<string,string> $data */
        $data = json_decode($response->getContent(), true);

        if (array_key_exists('token', $data)) {
            $this->token = $data['token'];
            return $this->token;
        }

        return null;
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        //set http client
        $this->client = static::createClient([], [
            'headers' => [
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ]
        ]);
        $this->client->disableReboot();

        //get services from container
        $container = static::getContainer();
        //get a Password hasher
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get('security.user_password_hasher');
        $this->hasher = $hasher;
        //get a doctrine entity manager
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $this->entityManager = $em;

        //prepare database connection for rollback
        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        self::ensureKernelShutdown();
    }
}
