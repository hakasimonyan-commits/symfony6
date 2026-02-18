<?php

namespace App\EventSubscriber;

use App\Event\UserAddSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UserSubscriber implements EventSubscriberInterface
{
    // Constructor — mailer-ը ստանալու համար
    public function __construct(
        private MailerInterface $mailer
    ) {}




    public static function getSubscribedEvents(): array
    {
        return [
            'userAdd.success' => 'sendAddUserMail',
        ];
    }

    public function sendAddUserMail(UserAddSuccessEvent $event)
    {
        $user = $event->getUser();

        $mail = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->to($user->getEmail())
            ->subject('Votre compte a été créé')
            ->text(
                "Bonjour, \n\n"
                    . "Votre compte a été créé avec succès. \n\n"
                    . "Cordialement, \n"
                    . "L'équipe MyApp"
            );

        //$this->mailer->send($mail);
    }
}
