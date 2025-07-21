<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DevisTypeForm extends AbstractType
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
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'required' => false,
                'attr' => [
                    'placeholder' => 'Téléphone'
                ]
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Veuillez détailler votre demande...',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                 'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Détails de la demande...*'
                 ],
                 
            ])
            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'choice_label' => 'nomPrestation',
                'label' => 'Prestation',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'placeholder' => 'Sélectionnez une prestation*',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('vehicule', VehiculeTypeForm::class) // ajout du form vehicule pour l'imbriquer
            ->add('consentement', CheckboxType::class, [
                'label' => 'Consentement',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'required' => true,
                'mapped' => false
            ])
            ->add('bearpot', HoneyPotType::class, [
                'label' => false,
                'required' => false,
            ])    
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
