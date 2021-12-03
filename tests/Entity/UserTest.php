<?php

namespace Tests\Entity;

use App\Entity\User;
use Monolog\Test\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class UserTest extends TestCase
{
    private UserPasswordHasher $passwordHasher;

    /** @test */
    public function we_can_instantiate_a_user(): void
    {
        //When create an instance of User class
        $user = new User();

        //We expect object is instance of User
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function we_can_set_and_retrieve_user_data(): void
    {
        //We have a user
        $user = new User();

        //When we set his properties
        $user
            ->setEmail('tester@email.com')
        ;
        $pwd = $this->passwordHasher->hashPassword($user, 'pwd');
        $user->setPassword($pwd);


        //We expect
        $username = $user->getUsername();
        $identifier = $user->getUserIdentifier();
        $email = $user->getEmail();
        $isValidPassword = $this->passwordHasher->isPasswordValid($user, 'pwd');
        $user->setRoles(['ROLE_TESTER']);
        $roles = $user->getRoles();
        $id = $user->getId();

        $this->assertEquals('tester@email.com', $identifier);
        $this->assertEquals('tester@email.com', $email);
        $this->assertTrue($isValidPassword, "The password submitted to verification isn't valid");
        $this->assertContains('ROLE_TESTER', $roles, "User don't have expected ROLE_TESTER");
        $this->assertContains('ROLE_USER', $roles, "User don't have expected role ROLE_USER");
        $this->assertNull($id);
    }

    protected function setUp(): void
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            User::class => ['algorithm' => 'auto'],
        ]);
        $this->passwordHasher = new UserPasswordHasher($passwordHasherFactory);
    }
}
