<?php

namespace Tests\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

class CreateUserCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    /** @test */
    public function that_we_can_execute_the_command_successfully(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(["pwdpwd", "yes"]);

        $commandTester->execute([
            "--email" => "tester@email.com",
            "--password" => "pwdpwd",
        ]);

        $output = $commandTester->getDisplay();

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString("User have been created!", $output);
    }

    /** @test */
    public function that_invalid_input_cancel_execution(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(["pwd", "yes"]);

        $commandTester->execute([
            "--email" => "tester@email.com",
            "--password" => "pwdpwd",
        ]);

        $output = $commandTester->getDisplay();

        $this->assertEquals(2, $commandTester->getStatusCode());
        $this->assertStringContainsString("No user have been created. You can try again with correct inputs.", $output);
    }

    /** @test */
    public function that_user_can_cancel_execution(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(["pwdpwd", "no"]);

        $commandTester->execute([
            "--email" => "tester@email.com",
            "--password" => "pwdpwd",
        ]);

        $output = $commandTester->getDisplay();

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString("Operation have been correctly cancelled", $output);
    }

}