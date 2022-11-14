<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('character1', NumberType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('character2', NumberType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('character3', NumberType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('character4', NumberType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('character5', NumberType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
