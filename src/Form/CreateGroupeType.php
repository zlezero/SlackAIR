<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\TypeGroupe;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CreateGroupeType extends AbstractType
{


    
    private EntityManager $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('typeGroupeId',ChoiceType::class,[
                
            'choices'=>$this->entityManager->getRepository(TypeGroupe::class)->findAll(),
            // "name" is a property path, meaning Symfony will look for a public
            
            
            //'data'=>$this->entityManager->getReference("App:TypeGroupe", 3),
            
            
            // a callback to return the label for a given choice
            // if a placeholder is used, its empty value (null) may be passed but
            // its label is defined by its own "placeholder" option
            'choice_label' => function(?TypeGroupe $typeGroupe) {
                return $typeGroupe ? $typeGroupe->getLabel() : '';
            },
            'choice_value' => function(?TypeGroupe $typeGroupe) {
                return $typeGroupe ? $typeGroupe->getId() : '';
            },
            // returns the html attributes for each option input (may be radio/checkbox)
            'choice_attr' => function(?TypeGroupe $typeGroupe) {
                return $typeGroupe ? ['class' => 'typeGroupe_'.strtolower($typeGroupe->getLabel())] : [];
            },                 
            
            ])
            ->add('CreateANewGroup', SubmitType::class)
        ;
    }
}
