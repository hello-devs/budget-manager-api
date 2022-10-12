<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserCreationDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserCreationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface      $entityManager,
        private readonly ValidatorInterface          $validator
    ) {
    }

    /**
     * @param UserCreationDto $data
     * @param Operation $operation
     * @param mixed[] $uriVariables
     * @param mixed[] $context
     * @return User
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if (!($data instanceof UserCreationDto)) {
            //todo log error info for devs
            throw new HttpException(500);
        }

        $user = new User();

        $password = $this->hasher->hashPassword($user, $data->plainPassword);
        $user
            ->setPassword($password)
            ->setEmail($data->email)
            ->setRoles($data->roles);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            //todo log error info for devs
            throw new HttpException(500);
        }

        $this->entityManager->persist($user);

        return $user;
    }
}
