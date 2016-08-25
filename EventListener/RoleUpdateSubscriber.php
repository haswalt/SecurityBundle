<?php

namespace Haswalt\SecurityBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Haswalt\SecurityBundle\Entity\User;

class RoleUpdateSubscriber implements EventSubscriber
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof User) {
            if ($eventArgs->hasChangedField('roles')) {
                $eventArgs->setNewValue('roles', $eventArgs->getOldValue('roles'));
            }
        }
    }
}
