<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface      $entityManager
    ) {
    }

    /**
     * @param $data
     * @param array<mixed> $context
     * @return bool
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     * @param array<mixed> $context
     * @return void
     */
    public function persist($data, array $context = []): void
    {
        if ($data->getPlainPassword()) {
            $hashedPassword = $this->hasher->hashPassword($data, $data->getPlainPassword());

            $data->setPassword($hashedPassword);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $data->eraseCredentials();
    }

    /**
     * @param User $data
     * @param array<mixed> $context
     * @return void
     */
    public function remove($data, array $context = []): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
