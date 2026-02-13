<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Service\MessageGenerator;
use App\Service\MailService;



final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'message' => '😊 Bienvenue sur la page Accueil !',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'Controller de page contact',
            'coordonnees' => [
                "Nom" => "Simonyan",
                "Prénom"  => "Hayka",
                "Adresse" => "Chez moi",
            ],

            'coordonnees1' => [
                "Nom" => "Simonyan",
                "Prénom"  => "Hayka",
                "Adresse" => "Chez moi",
            ]
        ]);
    }

    #[Route('/send-mail', name: 'app_send_mail')]
    public function sendTestMail(
        MailerInterface $mailer,
        MessageGenerator $msg
    ): Response {
        $email = (new Email())
            ->from('haka.simonyan@gmail.com')
            ->to('haka.simonyan@gmail.com')
            ->subject('Test mail')
            ->text($msg->getHappyMessage())
            ->html(
                $this->renderView('mail/email.html.twig', [
                    'message' => $msg->getHappyMessage()
                ])
            );

        $mailer->send($email);

        return new Response('Service page');
    }



    #[Route('/service', name: 'app_service')]
    public function service(MessageGenerator $msg): Response
    {
        return new Response($msg->getHappyMessage());
    }


    #[Route('/mail', name: 'app_mail')]
    public function sendMail(MailService $mailService): Response
    {
        $mailService->send(
            'haka.simonyan@gmail.com',
            'Bienvenue'
        );

        return new Response('Mail envoyé via service');
    }
}
