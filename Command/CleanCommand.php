<?php

namespace Haswalt\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('security:clean');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $services = [
            'oauth_server.access_token_manager' => 'Access Token',
            'oauth_server.refresh_token_manager' => 'Refresh Token',
        ];

        foreach ($services as $service => $name) {
            $instance = $this->getContainer()->get($service);
            $result = $instance->deleteExpired();
            $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage', $result, $name));
        }
    }
}
