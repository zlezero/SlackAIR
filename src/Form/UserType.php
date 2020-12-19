<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author CORREA Aminata
 * Ce formulaire permet de modifier les informations d'un utilisateur
 */

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class)
            ->add('age', IntegerType::class, ['required' => false, 'attr' => ['min' => 0, 'max' => 150]])
            ->add('profession', TextType::class, ['required' => false])
            ->add('DepartementId', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => function($departement){
                    return $departement->getNom();
                }
            ])
            ->add('annuler', ResetType::class)
            ->add('confirmer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}