<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $mailer;
    private $from;

    public function __construct(MailerInterface $mailer, string $from)
    {
        $this->mailer = $mailer;
        $this->from = $from;
    }

    public function send(string $to, string $message): void
    {
        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Service Mail')
            ->text($message)
            ->html('<h2>' . $message . '</h2>');

        $this->mailer->send($email);
    }
}
