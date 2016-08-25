<?php

namespace Haswalt\SecurityBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Haswalt\SecurityBundle\Entity\User;

class PasswordUpdateSubscriber implements EventSubscriber
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof User) {
            $encoded = $this->encodePassword($entity);
            $entity->setPassword($encoded);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof User) {
            if ($eventArgs->hasChangedField('password')) {
                $encoded = $this->encodePassword($entity);
                $eventArgs->setNewValue('password', $encoded);
            }
        }
    }

    private function encodePassword(User $user)
    {
        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($user->getPassword(), $user->getSalt());
    }
}
