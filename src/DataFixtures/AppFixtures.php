<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Root');
        $admin->setRoles(['ROLE_ADMIN']);

        $adminPasswordHash = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );

        $admin->setPassword($adminPasswordHash);
        $manager->persist($admin);


        $user = new User();
        $user->setEmail('test@test.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setRoles(['ROLE_USER']);

        $userPasswordHash = $this->passwordHasher->hashPassword(
            $user,
            '123'
        );

        $user->setPassword($userPasswordHash);

        $manager->persist($user);



        $employee = new User();
        $employee->setEmail('employee@test.com');
        $employee->setNom('Employee');
        $employee->setPrenom('Emploi');
        $employee->setRoles(['ROLE_EMPLOYEE']);

        $employeePasswordHash = $this->passwordHasher->hashPassword(
            $employee,
            '1234'
        );

        $employee->setPassword($employeePasswordHash);
        $manager->persist($employee);
        $manager->flush();
    }
}
