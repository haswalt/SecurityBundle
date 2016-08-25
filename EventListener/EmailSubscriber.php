<?php

namespace Haswalt\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Haswalt\SecurityBundle\SecurityEvents;
use Haswalt\SecurityBundle\Event\UserEvent;

use PhpAmqpLib\Message\AMQPMessage;

class EmailSubscriber implements EventSubscriberInterface
{
    protected $channel;

    protected $templating;

    public function __construct($channel, $templating)
    {
        $this->channel = $channel;
        $this->templating = $templating;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::REGISTER => [
                ['sendRegisterEmail'],
            ],
            SecurityEvents::FORGOT_PASSWORD => [
                ['sendForgotPasswordEmail'],
            ],
            SecurityEvents::RESET_PASSWORD => [
                ['sendResetPasswordEmail'],
            ],
        ];
    }

    public function sendRegisterEmail(UserEvent $event)
    {
        $body = $this->templating->render('HaswaltSecurityBundle:Email:welcome.html.twig', [
            'user' => $event->getUser(),
        ]);

        $data = [
            'name' => '',
            'email' => $event->getUser()->getUsername(),
            'subject' => 'Welcome',
            'body' => nl2br(strip_tags($body)),
            'html' => $body,
        ];

        $msg = new AMQPMessage(json_encode($data), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($msg, 'workers', 'mail.register');
    }

    public function setForgotPasswordEmail(UserEvent $event)
    {
        $body = $this->templating->render('HaswaltSecurityBundle:Email:forgot.html.twig', [
            'user' => $event->getUser(),
        ]);

        $data = [
            'name' => $event->getUser()->getProfile()->getFullName(),
            'email' => $user->getUsername(),
            'subject' => 'Password Recovery',
            'body' => nl2br(strip_tags($body)),
            'html' => $body,
        ];

        $msg = new AMQPMessage(json_encode($data), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($msg, 'workers', 'mail.password.forgot');
    }

    public function sendResetPasswordEmail(UserEvent $event)
    {
        $body = $this->templating->render('HaswaltSecurityBundle:Email:reset.html.twig', [
            'user' => $event->getUser(),
        ]);

        $data = [
            'name' => $event->getUser()->getProfile()->getFullName(),
            'email' => $user->getUsername(),
            'subject' => 'Password Reset',
            'body' => nl2br(strip_tags($body)),
            'html' => $body,
        ];

        $msg = new AMQPMessage(json_encode($data), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($msg, 'workers', 'mail.password.reset');
    }
}
