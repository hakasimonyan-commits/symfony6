<?php

namespace App\Controller;

// Օգտատիրոջ Entity-ն (մեկ user = մեկ տող DB-ում)
use App\Entity\User;

// User-ի ֆորմը (դաշտեր, վալիդացիա)
use App\Form\UserType;

// Repository՝ օգտատերերին DB-ից ստանալու համար
use App\Repository\UserRepository;

// Doctrine EntityManager (persist, flush, remove)
use Doctrine\ORM\EntityManagerInterface;

// Symfony-ի հիմնական controller-ը
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// HTTP հարցում և պատասխան
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Route attribute (Symfony 6)
use Symfony\Component\Routing\Attribute\Route;

// Գաղտնաբառերի ապահով hash անելու համար
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//  EVENT SYSTEM
use App\Event\UserAddSuccessEvent;
use App\Event\UserDeleteSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/user')] // Այս controller-ի բոլոր route-երը սկսվում են /user-ից
final class UserController extends AbstractController
{

    //  ՕԳՏԱՏԵՐԵՐԻ ՑՈՒՑԱԿ

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Բերում ենք բոլոր օգտատերերին DB-ից
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    // ADD — ՆՈՐ ՕԳՏԱՏԵՐ

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        UserPasswordHasherInterface $passwordHasher // Event dispatcher
    ): Response {
        // Ստեղծում ենք նոր դատարկ User օբյեկտ
        $user = new User();

        // Կապում ենք User-ը ֆորմի հետ
        $form = $this->createForm(UserType::class, $user, [
            'current_user' => $this->getUser(),
        ]);

        // Կարդում ենք request-ի տվյալները ֆորմի մեջ
        $form->handleRequest($request);

        // Եթե ֆորմը ուղարկված է և ճիշտ է լրացված
        if ($form->isSubmitted() && $form->isValid()) {
            // ADD — password hash
            $password = $form->get('password')->getData();

            $user->setPassword(
                $passwordHasher->hashPassword($user, $password)
            );


            // Վերցնում ենք role-ը form-ից (եթե կա)
            $selectedRole = $form->get('roles')?->getData();

            // Ստանում ենք ընթացիկ օգտատիրոջ իրական roles-ը
            $currentRoles = $this->getUser()?->getRoles() ?? [];



            //  Եթե ADMIN է
            if (in_array('ROLE_ADMIN', $currentRoles, true) && $selectedRole) {

                // Admin-ը կարող է ստեղծել Admin / Employee / User
                $user->setRoles([$selectedRole]);
            }

            //  Եթե EMPLOYEE է
            elseif (in_array('ROLE_EMPLOYEE', $currentRoles, true)) {

                // Employee-ը ՄԻՇՏ ստեղծում է ROLE_USER
                $user->setRoles(['ROLE_USER']);
            }

            // Պատրաստում ենք INSERT-ը
            $entityManager->persist($user);

            // Գրառում ենք անում database-ում
            $entityManager->flush();

            // ADD message
            $this->addFlash(
                'success',
                'L’utilisateur a été ajouté avec succès.'
            );
            // Ստեղծում ենք event՝ «օգտատերը հաջողությամբ ավելացվեց»
            $userEvent = new UserAddSuccessEvent($user);

            // Dispatch ենք անում event-ը
            // Այստեղից subscriber-ը կաշխատի
            $dispatcher->dispatch($userEvent, 'userAdd.success');

            // Վերադառնում ենք օգտատերերի ցուցակ
            return $this->redirectToRoute(
                'app_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        // Եթե GET է կամ ֆորմը սխալ է՝ ցույց ենք տալիս ֆորմը
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    //  SHOW — ՄԵԿ ՕԳՏԱՏԵՐ

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Symfony-ն ավտոմատ գտնում է User-ը ըստ id-ի
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }


    // EDIT — ՕԳՏԱՏԵՐԻ ՓՈՓՈԽՈՒՄ

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Ֆորմը կապում ենք արդեն գոյություն ունեցող User-ի հետ
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Վերցնում ենք password2 դաշտը (mapped չէ entity-ին)
            $password2 = $form->get('password2')->getData();

            // Եթե օգտատերը նոր գաղտնաբառ է գրել
            if ($password2) {
                // Hash ենք անում գաղտնաբառը և պահում
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $password2)
                );
            }

            // Վերցնում ենք ընտրված role-ը
            $selectedRole = $form->get('roles')->getData();

            // Միայն admin-ը կարող է փոխել role
            if ($this->isGranted('ROLE_ADMIN') && $selectedRole) {
                // ROLE_USER-ը միշտ պահում ենք
                $user->setRoles([$selectedRole, 'ROLE_USER']);
            }

            // UPDATE database-ում
            $entityManager->flush();

            // Success հաղորդագրություն
            $this->addFlash('success', 'L’utilisateur a été mis à jour avec succès.');

            return $this->redirectToRoute(
                'app_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    //  DELETE — ՕԳՏԱՏԵՐԻ ՋՆՋՈՒՄ

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
    ): Response {
        // Ստուգում ենք CSRF token-ը
        if ($this->isCsrfTokenValid(
            'delete' . $user->getId(),
            $request->getPayload()->getString('_token')
        )) {
            // Ջնջում ենք օգտատիրոջը DB-ից
            $entityManager->remove($user);
            $entityManager->flush();

            //dispatcher l'événement userDelete.success
            $dispatcher->dispatch(
                new UserDeleteSuccessEvent($user),
                'user.delete.success'
            );
        }


        return $this->redirectToRoute(
            'app_user_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}
