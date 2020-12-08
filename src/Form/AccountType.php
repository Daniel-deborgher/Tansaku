<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
        /**
     * Permet d'avoir la configuration de base d'un champ : 
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    
    private function getConfiguration ($label, $placeholder, $options = []){
        return array_merge([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
            ], $options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom ..."))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom ..."))
            ->add('picture', FileType::class, [
                "label" => "Ajouter une image",
                'attr' => [
                    'placeholder' => "(optionel)"
                ],
                "mapped" => false,
                "constraints" => [
                    new File([
                        "mimeTypes" => ["image/jpeg", "image/png"],
                        "mimeTypesMessage" => "Formats autorisés : jpg, png",
                        "maxSize" => "2048k",
                        "maxSizeMessage" => "le fichier ne doit pas dépasser 2mo"
                        ])
                ]
            ])
            ->add('description', TextareaType::class, $this->getConfiguration("Décrivez-vous", "Décrivez nous vos passions (optionel)"))
        ;
        $builder->get('picture')->setRequired(false);
        $builder->get('description')->setRequired(false);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
