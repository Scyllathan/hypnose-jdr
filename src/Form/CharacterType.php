<?php

namespace App\Form;

use App\Entity\Character;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('age', NumberType::class)
            ->add('disease', TextType::class)
            ->add('story')
            ->add('powers')
            ->add('money', NumberType::class)
            ->add('bag')
            ->add('stamina', NumberType::class)
            ->add('strength', NumberType::class)
            ->add('agility', NumberType::class)
            ->add('speed', NumberType::class)
            ->add('charisma', NumberType::class)
            ->add('intelligence', NumberType::class)
            ->add('resilience', NumberType::class)
            ->add('luck', NumberType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
