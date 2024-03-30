<?php

namespace App\Form;

use App\DTO\NewPasswordDTO;
use App\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['required'],
                'invalid_message' => 'The passwords do not match',
                'first_options' => [
                    'label' => sprintf('%s (minimum %d characters)', $options['label'], PasswordRequirements::MIN_LENGTH),
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'min' => PasswordRequirements::MIN_LENGTH,
                        'max' => PasswordRequirements::MAX_LENGTH,
                    ],

                ],
                'second_options' => [
                    'label' => 'Repeat password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'min' => PasswordRequirements::MIN_LENGTH,
                        'max' => PasswordRequirements::MAX_LENGTH,
                    ],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewPasswordDTO::class,
            'label' => 'New password',
            'required' => false,
        ]);

        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('required', 'bool');
    }
}
