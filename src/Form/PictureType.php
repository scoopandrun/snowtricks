<?php

namespace App\Form;

use App\Entity\Picture;
use App\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Picture',
                'help' => sprintf('Max size %s', FileManager::getUploadMaxFilesize('auto', true)),
                'required' => true,
                'constraints' => [
                    new Image(
                        maxSize: FileManager::getUploadMaxFilesize(),
                    ),
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'data-max-size' => FileManager::getUploadMaxFilesize('B'),
                    'data-controller' => 'file',
                ],
            ])
            ->add('description', TextType::class, [
                'required' => true,
            ])
            ->add('setAsMainPicture', RadioType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
