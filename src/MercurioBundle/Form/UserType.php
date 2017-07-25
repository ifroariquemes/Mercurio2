<?php

namespace MercurioBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, ['label' => 'Nome completo'])
                ->add('email', EmailType::class, ['label' => 'E-mail'])
                ->add('role', ChoiceType::class, [
                    'label' => 'Tipo de conta',
                    'choices' => [
                        'Usuário' => 'ROLE_USER',
                        'Administrador' => 'ROLE_ADMIN'
                    ]
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Senha'],
                    'second_options' => ['label' => 'Confirme a senha'],
                    'invalid_message' => 'As senhas precisam ser iguais'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MercurioBundle\Entity\User',
        ]);
    }

}
