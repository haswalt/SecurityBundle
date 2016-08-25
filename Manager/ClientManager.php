<?php

namespace Haswalt\SecurityBundle\Manager;

use Haswalt\SecurityBundle\Entity\Client;

class ClientManager extends AbstractManager
{
    public function updateClient(Client $client)
    {
        $this->em->persist($client);
        $this->em->flush();
    }

    public function findClientByPublicId($id)
    {
        $repository = $this->em->getRepository('HaswaltSecurityBundle:Client');

        return $repository->findOneByRandomId($id);
    }
}
