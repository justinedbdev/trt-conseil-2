<?php

namespace App\Form;

use App\Entity\JobOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class JobOfferFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'annonce',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Titre de l\'annonce'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un titre valide.']),
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville du poste',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Ville du poste'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville valide.']),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du poste',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du poste'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une description valide.']),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
