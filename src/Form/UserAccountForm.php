<?php

namespace App\Form;

use App\DTO\UserInformationDTO;
use App\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserAccountForm extends AbstractType
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
                'label' => 'Profile picture',
                'help' => sprintf('Max size %s', FileManager::getUploadMaxFilesize('auto', true)),
                'constraints' => [
                    new Image(
                        maxSize: FileManager::getUploadMaxFilesize(),
                    ),
                ],
                'attr' => [
                    'data-max-size' => FileManager::getUploadMaxFilesize('B'),
                    'data-controller' => 'file',
                ],
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
            ->add('newPassword', NewPasswordType::class, [
                'required' => false,
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
