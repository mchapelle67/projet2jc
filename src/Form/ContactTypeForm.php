<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'placeholder' => 'Nom*'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'placeholder' => 'Prénom*'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'placeholder' => 'Email*'
                ]
            ])
            ->add('tel', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'placeholder' => 'Téléphone'
                ]
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 3
                ],
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'placeholder' => 'Veuillez détailler votre demande ...*',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
