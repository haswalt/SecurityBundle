<?php

namespace Haswalt\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use OAuth2\OAuth2;

use Haswalt\SecurityBundle\Entity\Client;

class ListClientsCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('security:list-clients');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $rows = [];

        $clients = $em->getRepository('HaswaltSecurityBundle:Client')
            ->findAll();

        foreach ($clients as $client) {
            $rows[] = [
                $client->getId(),
                $client->getRandomId(),
                $client->getSecret(),
            ];
        }

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
