<?php

namespace App\Service;

class MessageGenerator
{
    private array $messages = [
        'Bravo Hayka ',
        'Excellent travail ',
        'Continue comme ça ',
        'Très bon travail ',
    ];

    public function getHappyMessage(): string
    {
        $index = random_int(0, count($this->messages) - 1);

        return $this->messages[$index];
    }
}
