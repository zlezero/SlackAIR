<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\TypeGroupe;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('description', TextType::class, [
                'required' => false
            ])
            ->add('typeGroupeId', EntityType::class, [
                'class'=>TypeGroupe::class,
                'choices'=>$this->entityManager->getRepository(TypeGroupe::class)->getTypeGroupeExceptDM(),
                'choice_label' => function(?TypeGroupe $typeGroupe) {
                    return $typeGroupe ? $typeGroupe->getLabel() : '';
                },
                'choice_value' => function(?TypeGroupe $typeGroupe) {
                    return $typeGroupe ? $typeGroupe->getId() : null;
                },
                'choice_attr' => function(?TypeGroupe $typeGroupe) {
                    return $typeGroupe ? ['class' => 'typeGroupe_'.strtolower($typeGroupe->getLabel())] : [];
                },
                'label' => "Type groupe"
            ])
            ->add('invitations', EntityType::class, [
                'class' => User::class,
                'choices'=> $this->entityManager->getRepository(User::class)->getAllUsersExceptMe(array_key_exists("data", $options) ? ($options["data"]->getIdProprietaire() ? $options["data"]->getIdProprietaire()->getId() : -1) : -1),
                'multiple'=> true,
                'choice_label' => function(?User $user) {
                    return $user ? $user->getPseudo() : '';
                },
                'choice_value'=> function(?User $user) {
                    return $user ? $user->getId() : '';
                },
                'mapped' => false,
                'choice_attr' => function(?User $user) {
                    return $user ? ['class' => 'invitation_'.strtolower($user->getUsername())] : [];
                },
                ],
            )
            ->add('annuler', ResetType::class)
            ->add('confirmer', SubmitType::class)
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
        ]);
    }

}
