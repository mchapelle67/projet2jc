<?php

namespace App\Form;

use App\Entity\Rdv;
use App\Entity\Vehicule;
use App\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RdvTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_rdv', HiddenType::class)
            ->add('nom', TextType::class, [
                'label' => 'Nom*',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom*',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email*',
            ])
            ->add('tel', TelType::class, [
                'label' => 'Téléphone',
                'required' => false
            ])
            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'choice_label' => 'nomPrestation',
                'label' => 'Prestation*',
                'placeholder' => 'Sélectionnez une prestation'
            ])
            ->add('vehicule', VehiculeTypeForm::class) // ajout du form vehicule pour l'imbriquer
            ->add('consentement', CheckboxType::class, [
                'label' => false,
                'required' => true,
                'mapped' => false
            ])
        ;

        // transformer le champ date_rdv pour gérer l'affichage et la soumission
        // on utilise un CallbackTransformer pour transformer la valeur entre l'affichage et la soumission
         $builder->get('date_rdv')->addModelTransformer(new CallbackTransformer(
            function ($dateAsObject) {
                // transforme l'objet en string pour le champ (affichage)
                return $dateAsObject instanceof \DateTimeInterface ? $dateAsObject->format('Y-m-d\TH:i') : $dateAsObject;
            },
            function ($dateAsString) {
                // transforme la string en DateTimeImmutable pour l'entité
                return $dateAsString ? \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $dateAsString) : null;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
        ]);
    }
}
