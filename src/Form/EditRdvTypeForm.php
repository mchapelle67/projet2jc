<?php

namespace App\Form;

use App\Entity\Rdv;
use App\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class EditRdvTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_rdv', DateTimeType::class, [
                'label' => 'date_rdv',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'choice_label' => 'nomPrestation',
                'label' => 'Prestation',
                'label_attr' => [
                    'class' => 'visually-hidden'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
        ]);
    }
}
