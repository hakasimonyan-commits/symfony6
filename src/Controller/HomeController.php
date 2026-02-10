<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
