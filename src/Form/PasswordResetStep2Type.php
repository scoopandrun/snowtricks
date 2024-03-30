<?php

namespace App\Form;

use App\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', NewPasswordType::class, [
                'required' => true,
                'label' => 'Password',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class,
            'validations_groups' => ['password_reset_step_2'],
        ]);
    }
}
