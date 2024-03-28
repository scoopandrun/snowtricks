<?php

namespace App\Form;

use App\DTO\UserInformationDTO;
use App\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'username',
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('profilePicture', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Image(),
                ]
            ])
            ->add('removeProfilePicture', CheckboxType::class, [
                'required' => false,
            ])
            ->add('currentPassword', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'invalid_message' => 'The passwords do not match',
                'first_options' => [
                    'label' => sprintf('New password (minimum %d characters)', PasswordRequirements::MIN_LENGTH),
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
            'data_class' => UserInformationDTO::class,
            'validation_groups' => ['account_update'],
        ]);
    }
}
