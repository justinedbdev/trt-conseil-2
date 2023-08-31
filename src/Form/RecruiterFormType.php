<?php

namespace App\Form;

use App\Entity\Recruiter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecruiterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de l\'entreprise'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom d\'entreprise.']),
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de l\'entreprise',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse de l\'entreprise'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une adresse.']),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recruiter::class,
        ]);
    }
}
