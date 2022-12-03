<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: "app:create-user",
    description: "Create a new user.",
    aliases: ["app:add-user"]
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly ValidatorInterface          $validator,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface      $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'USER CREATOR',
            '============',
            '',
            'You are about to create a user',
        ]);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');


        //get email
        /** @var string $email */
        $email = $input->getOption("email") ?? $helper->ask($input, $output, new Question("Enter email: "));

        //get password
        $question = new Question("Enter password: ");
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        /** @var string $plainPassword */
        $plainPassword = $input->getOption("password") ?? $helper->ask($input, $output, $question);

        //confirm password
        $question = new Question("Confirm password: ");
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        /** @var string $confirmPassword */
        $confirmPassword = $helper->ask($input, $output, $question);

        //get role
        /** @var string $role */
        $role = $input->getOption("role") ?? "ROLE_USER";

        $output->writeln(["_____________", "", "Verification:"]);

        //Set user data
        $user = new User();
        $user->setPlainPassword($plainPassword);
        $hashedPwd = $this->hasher->hashPassword($user, $plainPassword);

        $user
            ->setEmail($email)
            ->setPassword($hashedPwd)
            ->setRoles([$role]);

        //Check input validity
        /** @var ConstraintViolationList $violations */
        $violations = $this->validator->validate($confirmPassword, [
            new IdenticalTo($plainPassword, null, "Password and confirm password inputs should be identical")
        ]);
        $violations->addAll($this->validator->validate($user));

        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $output->writeln([$violation->getInvalidValue(), "<error>" . $violation->getMessage() . "</error>", ""]);
            }

            $output->writeln([
                "<info>No user have been created. You can try again with correct inputs.</info>",
                ""
            ]);

            return Command::INVALID;
        }
        $output->writeln([
            ". . . . . . .",
            "",
            "Verified!",
            "_____________"
        ]);

        //confirm creation
        $output->writeln("Please confirm creation of user \"$email\" with role: $role");

        $question = new ConfirmationQuestion('Continue with this action?', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("<info>Operation have been correctly cancelled</info>");
            return Command::SUCCESS;
        }

        //save user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(["","<info>User have been created!</info>", ""]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to create a user...')
            ->addOption('email', 'u', InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'User password')
            ->addOption("role", "r", InputOption::VALUE_OPTIONAL, "User role");
    }
}
