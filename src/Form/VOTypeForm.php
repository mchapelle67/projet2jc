<?php

namespace App\Form;

use App\Entity\VO;
use App\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VOTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque*',
                'attr' => [
                    'placeholder' => 'Entrez la marque du véhicule'],
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle*',
                'attr' => [
                    'placeholder' => 'Entrez le modèle du véhicule'],
            ])
            ->add('prix', TextType::class, [
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'Entrez le prix du véhicule'],
                'required' => false,
            ])
            ->add('anneeFabrication', TextType::class, [
                'label' => 'Année de fabrication',
                'attr' => [
                    'placeholder' => 'mm/aaaa'],
                'required' => false,
            ])
            ->add('carburant', ChoiceType::class, [
                'label' => 'Carburant',
                'choices' => [
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Électrique' => 'electrique',
                    'Hybride' => 'hybride',
                    'Ethanol' => 'ethanol',
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Sélectionnez le type de carburant',
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Entrez une description du véhicule',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('url', TextType::class, [
                'label' => 'Leboncoin',
                'attr' => ['placeholder' => 'Lien vers l\'annonce Leboncoin'],
                'required' => false,
            ])
            ->add('km', IntegerType::class, [
                'label' => 'Kilométrage',
                'attr' => ['
                    placeholder' => 'Entrez les km',
                    'min' => 0,
                    'max' => 9999999
                ],
                'required' => false,
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'multiple' => true
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/webp',
                                    'image/jpg'
                                ],
                                'mimeTypesMessage' => 'Veuillez uploader une image valide (jpg, jpeg, png, gif, webp)',
                            ]),
                        ],
                    ]),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VO::class,
        ]);
    }
}
