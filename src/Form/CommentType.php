<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isVerifiedUser = $options['is_verified_user'];

        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => !$isVerifiedUser ? ['disabled' => 'disabled', 'title' => 'Vous devez valider votre e-mail pour pouvoir poster un commentaire'] : [],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => !$isVerifiedUser ? ['disabled' => 'disabled', 'title' => 'Vous devez valider votre e-mail pour pouvoir poster un commentaire'] : [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'is_verified_user' => false,
        ]);
    }
}