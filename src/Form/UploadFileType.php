<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * @author CORREA Aminata
 * Ce formulaire permet de télécharger un fichier
 */

class UploadFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => '',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'maxSizeMessage' =>  'Votre fichier dépasse la taille maximale.'
                        ])
                    ],
                'attr'=>['class'=>'input']
            ])
            ->add('confirmer', SubmitType::class,
                ['attr'=>['style'=>'display:none']]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}