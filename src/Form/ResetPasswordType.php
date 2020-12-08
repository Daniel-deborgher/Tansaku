<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('hash', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => "Les mots de passes doivent être identiiques",
            'required' => true,
            'first_options' => [
                'label' => 'Saisir votre mot de passe'
            ],
            'second_options' => [
                'label' => 'Confirmez votre mot de passe'
            ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}