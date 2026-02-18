<?php

namespace App\EventListener;

use App\Event\UserDeleteSuccessEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UserDeleteListener
{
    public function __construct(private MailerInterface $mailer) {}

    public function onUserDelete(UserDeleteSuccessEvent $event): void
    {
        $user = $event->getUser();

        $email = (new Email())
            ->from('admin@test.com')
            ->to($user->getEmail())
            ->subject('Compte supprimé')
            ->text('Votre compte a été supprimé.');

        $this->mailer->send($email);
    }
}
