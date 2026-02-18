<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\User\UserInterface;



class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email');

        $currentUser = $options['current_user'];

        $roleChoices = [
            'Utilisateur' => 'ROLE_USER',
        ];

        if ($currentUser instanceof UserInterface && in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            $roleChoices['Employee'] = 'ROLE_EMPLOYEE';
            $roleChoices['Admin'] = 'ROLE_ADMIN';
            'ROLE_EMPLOYEE';
            'ROLE_USER';
        }

        $builder->add('roles', ChoiceType::class, [
            'label' => 'Rôle',
            'choices' => $roleChoices,
            'expanded' => false,
            'multiple' => false,
            'mapped' => false,
            'placeholder' => 'Sélectionnez un rôle',
        ])
            ->add('nom')
            ->add('prenom');

        // ecoute d'évenement

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {

            $user = $event->getData();
            $form = $event->getForm();

            // Création
            if (!$user || null === $user->getId()) {

                $form->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                ]);
            } else {

                // Édition
                $form->add('password', HiddenType::class);

                $form->add('password2', PasswordType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Nouveau mot de passe',
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current_user' => null,
        ]);
    }
}
