<?php

namespace Haswalt\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use OAuth2\OAuth2;

use Haswalt\SecurityBundle\Entity\Client;

class CreateClientCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('security:create-client')
            ->addOption('clients', null, InputOption::VALUE_REQUIRED, 'Number of clients to create', 1);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $rows = [];

        for ($i = 0; $i < $input->getOption('clients');$i++) {
            $client = new Client();
            $client->setRandomId($this->generateToken());
            $client->setSecret($this->generateToken());
            $client->setAllowedGrantTypes([
                OAuth2::GRANT_TYPE_REFRESH_TOKEN,
                OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                OAuth2::GRANT_TYPE_IMPLICIT,
            ]);

            $em->persist($client);
            $em->flush();

            $rows[] = [
                $client->getId(),
                $client->getRandomId(),
                $client->getSecret(),
            ];
        }

        $output->writeln(sprintf('Created %d clients:', $input->getOption('clients')));

        $table = new Table($output);
        $table
            ->setHeaders(['#', 'ID', 'Secret'])
            ->setRows($rows);
        $table->render();
    }

    private function generateToken()
    {
        $bytes = false;
        if (function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win')) {
            $bytes = openssl_random_pseudo_bytes(32, $strong);
            if (true !== $strong) {
                $bytes = false;
            }
        }
        // let's just hope we got a good seed
        if (false === $bytes) {
            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }
        return base_convert(bin2hex($bytes), 16, 36);
    }
}
