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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $output->writeln(['USER CREATOR', '============', '', 'You are about to create a user',]);

        //Get user Data
        list($email, $plainPassword, $confirmPassword, $role) = $this->getUserData($input, $helper, $output);

        $output->writeln(["_____________", "", "Verification:"]);

        //Set user data
        $user = $this->setupUserWithData($plainPassword, $email, $role);

        //Check inputs validity
        $areInputsValid = $this->checkInputsValidity($confirmPassword, $plainPassword, $user, $output);

        //Exit with invalid status if inputs are invalid.
        if (!$areInputsValid) {
            return Command::INVALID;
        }

        $output->writeln([". . . . . . .", "", "Verified!", "_____________"]);

        //Ask confirmation before user creation
        $confirmation = $this->askConfirmationBeforeUserCreation($output, $email, $role, $helper, $input);

        if (!$confirmation) {
            return Command::SUCCESS;
        }

        //save user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(["", "<info>User have been created!</info>", ""]);

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

    /**
     * @param OutputInterface $output
     * @param string $email
     * @param string $role
     * @param QuestionHelper $helper
     * @param InputInterface $input
     * @return bool
     */
    public function askConfirmationBeforeUserCreation(OutputInterface $output, string $email, string $role, QuestionHelper $helper, InputInterface $input): bool
    {
        $output->writeln("Please confirm creation of user \"$email\" with role: $role");

        $confirmation = true;
        $question3 = new ConfirmationQuestion('Continue with this action?', false);
        if (!$helper->ask($input, $output, $question3)) {
            $output->writeln("<info>Operation have been correctly cancelled</info>");
            $confirmation = false;
        }
        return $confirmation;
    }

    /**
     * @param string $confirmPassword
     * @param string $plainPassword
     * @param User $user
     * @param OutputInterface $output
     * @return bool
     */
    public function checkInputsValidity(string $confirmPassword, string $plainPassword, User $user, OutputInterface $output): bool
    {
        /** @var ConstraintViolationList $violations */
        $violations = $this->validator->validate($confirmPassword, [
            new IdenticalTo($plainPassword, null, "Password and confirm password inputs should be identical")
        ]);
        $violations->addAll($this->validator->validate($user));

        $areInputsValid = 0 === count($violations);

        if (!$areInputsValid) {
            foreach ($violations as $violation) {
                $output->writeln([$violation->getInvalidValue(), "<error>" . $violation->getMessage() . "</error>", ""]);
            }

            $output->writeln([
                "<info>No user have been created. You can try again with correct inputs.</info>",
                ""
            ]);
        }
        return $areInputsValid;
    }

    /**
     * @param string $plainPassword
     * @param string $email
     * @param string $role
     * @return User
     */
    public function setupUserWithData(string $plainPassword, string $email, string $role): User
    {
        $user = new User();
        $user->setPlainPassword($plainPassword);
        $hashedPwd = $this->hasher->hashPassword($user, $plainPassword);

        $user
            ->setEmail($email)
            ->setPassword($hashedPwd)
            ->setRoles([$role]);
        return $user;
    }

    /**
     * @param InputInterface $input
     * @param QuestionHelper $helper
     * @param OutputInterface $output
     * @return string[]
     */
    public function getUserData(InputInterface $input, QuestionHelper $helper, OutputInterface $output): array
    {
        //getEmail
        /** @var string $email */
        $email = $input->getOption("email") ?? $helper->ask($input, $output, new Question("Enter email: "));

        //get password
        $question1 = new Question("Enter password: ");
        $question1->setHidden(true);
        $question1->setHiddenFallback(false);
        /** @var string $plainPassword */
        $plainPassword = $input->getOption("password") ?? $helper->ask($input, $output, $question1);

        //confirm password
        $question2 = new Question("Confirm password: ");
        $question2->setHidden(true);
        $question2->setHiddenFallback(false);
        /** @var string $confirmPassword */
        $confirmPassword = $helper->ask($input, $output, $question2);

        //get role
        /** @var string $role */
        $role = $input->getOption("role") ?? "ROLE_USER";
        return array($email, $plainPassword, $confirmPassword, $role);
    }
}
