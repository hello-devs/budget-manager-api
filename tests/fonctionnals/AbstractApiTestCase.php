<?php

namespace Tests\fonctionnals;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiTestCase extends ApiTestCase
{
    protected Client $client;
    protected Client $clientWithToken;
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
     * @param string $method
     * @param string $url
     * @param string $token
     * @param array<string,mixed> $json
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    protected function requestWithJwt(string $method, string $url, string $token, array $json = []): ResponseInterface
    {
        return $this->client->request($method, $url, [
            'headers' => [
                "Authorization" => "Bearer $token",
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ],
            "json" => $json
        ]);
    }

    /**
     * @param string $authenticationUrl
     * @param array<string,string> $body
     * @return string
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function getToken(string $authenticationUrl, array $body = []): string
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

        return "null";
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

        unset($this->client);
        unset($this->clientWithToken);

        self::ensureKernelShutdown();
    }
}
