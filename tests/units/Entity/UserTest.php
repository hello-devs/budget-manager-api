<?php

namespace Tests\units\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
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
        $plainPass = 'pwd';

        //When we set his properties
        $pwd = $this->passwordHasher->hashPassword($user, $plainPass);

        $user
            ->setEmail('tester@email.com')
            ->setPlainPassword($plainPass)
            ->setPassword($pwd)
            ->setRoles(['ROLE_TESTER'])
        ;

        $username = $user->getUsername();
        $identifier = $user->getUserIdentifier();
        $email = $user->getEmail();
        $plainPassword = $user->getPlainPassword();
        $isValidPassword = $this->passwordHasher->isPasswordValid($user, 'pwd');
        $roles = $user->getRoles();
        $id = $user->getId();

        //We expect
        $this->assertEquals('tester@email.com', $identifier);
        $this->assertEquals('tester@email.com', $email);
        $this->assertEquals('tester@email.com', $username);
        $this->assertEquals('pwd', $plainPassword);
        $this->assertTrue($isValidPassword, "The password submitted to verification isn't valid");
        $this->assertContains('ROLE_TESTER', $roles, "User don't have expected ROLE_TESTER");
        $this->assertContains('ROLE_USER', $roles, "User don't have expected role ROLE_USER");
        $this->assertNull($id);
        $this->assertEmpty($user->getTransaction());
        $this->assertEmpty($user->getBudget());
        $this->assertInstanceOf(Collection::class, $user->getBudget());
        $this->assertInstanceOf(Collection::class, $user->getTransaction());
    }

    protected function setUp(): void
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            User::class => ['algorithm' => 'auto'],
        ]);
        $this->passwordHasher = new UserPasswordHasher($passwordHasherFactory);
    }
}
