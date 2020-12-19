<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @author ZONCHELLO Sébastien
 * Ce formulaire permet de télécharger sa photo de profil
 */

class UploadPdpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pdp', FileType::class, [
                'label' => '',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Merci de sélectionner une image valide',
                        ])
                    ],
                'attr'=>['class'=>'input']
            ])
            ->add('confirmer', SubmitType::class,
                ['attr'=>['style'=>'display:none']]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {}
    
}