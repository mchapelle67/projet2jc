<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Entity\Carburant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class VehiculeTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('km', IntegerType::class, [
                'label' => 'Kilométrage',
                'attr' => ['placeholder' => 'Entrez le kilométrage'],
                'required' => false
            ])
            ->add('anneeFabrication', DateType::class, [
                'widget' => 'single_text',
                'placeholder' => 'Année de fabrication',
                'required' => false,    
                'attr' => ['placeholder' => 'ex : 2020'],
                'html5' => false, // pour éviter le calendrier natif
            ])
            ->add('carburant', EntityType::class, [
                'class' => Carburant::class,
                'choice_label' => 'id',
                'label' => 'Carburant',
                'placeholder' => 'Sélectionnez le type de carburant',
                'required' => false,
                'expanded' => false,
                'multiple' => false
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
