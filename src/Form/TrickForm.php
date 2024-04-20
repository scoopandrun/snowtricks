<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Difficulty;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class TrickForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
            ])
            ->add('pictures', CollectionType::class, [
                'entry_type' => PictureType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'data-controller' => 'collection-edit',
                    'data-add-button-text' => 'Add a picture',
                ],
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new Valid(),
                ],
                'attr' => [
                    'data-controller' => 'collection-edit',
                    'data-add-button-text' => 'Add a video',
                ],
            ])
            ->add('difficulty', EntityType::class, [
                'class' => Difficulty::class,
                'choice_label' => 'value',
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
