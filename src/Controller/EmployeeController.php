<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

final class EmployeeController extends AbstractController
{
    #[Route('/employee', name: 'app_employee')]
    public function index(): Response
    {
        return $this->render('employee/index.html.twig');
    }

    // src/Controller/EmployeeController.php

    #[Route('/employee/users', name: 'app_employee_users')]
    public function users(UserRepository $userRepository): Response
    {


        if ($this->isGranted('ROLE_ADMIN')) {
            // ADMIN voit tout
            $users = $userRepository->findAll();
        } else {
            // EMPLOYEE ne voit PAS les admins
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.roles NOT LIKE :admin')
                ->setParameter('admin', '%ROLE_ADMIN%')
                ->getQuery()
                ->getResult();
        }

        return $this->render('employee/users.html.twig', [
            'users' => $users,
        ]);
    }
}
