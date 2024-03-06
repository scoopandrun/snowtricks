<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->onPostSubmit(...));
    }

    private function onPostSubmit(PostSubmitEvent $event): void
    {
        /** @var Trick */
        $trick = $event->getData();

        // Existing trick
        if (!is_null($trick->getId())) {
            $trick->setUpdatedAt(new \DateTimeImmutable());
        }

        $slugger = new AsciiSlugger();
        $slug = strtolower($slugger->slug((string) $trick->getName()));
        $trick->setSlug($slug);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
