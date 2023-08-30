<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',  EmailType::class, [
                'label' => 'Votre email',
                'required' => true,
                'attr' => [
                    'class' => 'mt-2',
                    'placeholder' => 'Entrez votre email'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un email valide !']),
                    new Email(['mode' => 'html5', 'message' => 'L\'email : {{ value }} n\'est pas valide']),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les conditions d\'utilisation',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation pour pouvoir vous inscrire.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'options' => [
                    'attr' => [
                        'class' => 'password-field',
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Entrez votre mot de passe'
                    ]
                ],
                'required' => true,
                'first_options'  => [
                    'label' => 'Entrez votre mot de passe',
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                        new Length(['min' => 6, 'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères', 'max' => 4096,]),
                    ]],
                'second_options' => [
                    'label' => 'Validez votre mot de passe',
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                        new Length(['min' => 6, 'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères', 'max' => 4096,]),
                    ]],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Vous êtes' ,
                'expanded' => true,
                'choices' => [
                    'Candidat' => 'ROLE_CANDIDAT',
                    'Recruteur' => 'ROLE_RECRUITER',
                ],
                'multiple' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une proposition.'])
                ]
            ]);
            
        $builder->get('roles')
        ->addModelTransformer(new CallbackTransformer(
            function ($rolesAsArray): string {
                return implode(',', $rolesAsArray);
            },
            function ($rolesAsString): array {
                return explode(',', $rolesAsString);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
