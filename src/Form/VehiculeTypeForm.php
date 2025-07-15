<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Entity\Carburant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class VehiculeTypeForm extends AbstractType
{

    private ?string $marque = null;
    private ?string $modele = null;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', HiddenType::class)
            ->add('modele', HiddenType::class)
            ->add('km', IntegerType::class, [
                'label' => 'Kilométrage',
                'label_attr' => [
                    'class' => 'visually-hidden'
                    ],
                'attr' => [
                    'placeholder' => 'Entrez le kilométrage',
                    'min' => 0,
                    'max' => 9999999
                ],
                'required' => false
            ])
            ->add('anneeFabrication', IntegerType::class, [
                'label' => 'Année de fabrication',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'required' => false,    
                'attr' => [
                    'placeholder' => "Entrez l'année de fabrication",
                    'min' => 1900,
                    'max' => date('Y')
                ],
            ])
            ->add('carburant', EntityType::class, [
                'class' => Carburant::class,
                'choice_label' => 'typeCarburant',
                'label' => 'Type de carburant',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'placeholder' => 'Sélectionnez le type de carburant',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
