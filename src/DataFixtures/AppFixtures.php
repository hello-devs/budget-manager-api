<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = new User();

        $admin
            ->setEmail("admin@admin.com")
            ->setPassword($this->passwordHasher->hashPassword($admin, "pwdpwd"))
            ->setRoles(['ROLE_ADMIN'])
            ;
        $manager->persist($admin);

        $manager->flush();
    }
}
