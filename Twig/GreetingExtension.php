<?php

namespace HurlyRate\SecurityBundle\Twig;

class GreetingExample extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'greeting' => new \Twig_SimpleFunction('greeting', [$this, 'getGreeting']),
        ];
    }

    public function getGreeting()
    {
        $date = new \DateTime();
        $hour = $date->format('H');

        if ($hour < 12) {
            return 'Morning';
        } elseif ($hour > 12 && $hour < 17) {
            return 'Afternoon';
        } else {
            return 'Evening';
        }
    }

    public function getName()
    {
        return 'haswalt_greeting';
    }
}
