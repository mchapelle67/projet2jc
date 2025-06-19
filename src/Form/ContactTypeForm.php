<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom *',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom *',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *',
            ])
            ->add('tel', TelType::class, [
                'label' => 'Téléphone',
                'required' => false
            ])
            ->add('text', TextType::class, [
                'label' => 'Message *',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Votre message ici...'
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
