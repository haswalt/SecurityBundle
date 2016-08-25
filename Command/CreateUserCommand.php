<?php

namespace Haswalt\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Haswalt\SecurityBundle\Entity\User;

class CreateUserCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('security:create-user');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $validator = $this->getContainer()->get('validator');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('HaswaltSecurityBundle:User');

        $usernameQuestion = new Question('Username (email): ');
        $usernameQuestion->setValidator(function($answer) use ($validator, $repository) {
            $trimmed = trim($answer);

            // check for an email
            $violations = $validator->validate($trimmed, [
                new \Symfony\Component\Validator\Constraints\Email(),
                new \Symfony\Component\Validator\Constraints\NotBlank(),
            ]);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                throw new \RuntimeException(join(' and ', $errors));
            }

            if (!$repository->checkAvailability($trimmed)) {
                throw new \RuntimeException('Username already taken');
            }

            return $trimmed;
        });

        $username = $helper->ask($input, $output, $usernameQuestion);

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(function($answer) use ($validator) {
            $trimmed = trim($answer);

            // check for an email
            $violations = $validator->validate($trimmed, [
                new \Symfony\Component\Validator\Constraints\NotBlank(),
            ]);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                throw new \RuntimeException(join(' and ', $errors));
            }

            return $trimmed;
        });

        $password = $helper->ask($input, $output, $passwordQuestion);

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

        $output->writeln('<comment>New user created</comment>');
    }
}
