<?php

namespace App\Form;

use App\Entity\CommentReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'Raison du signalement',
                'choices' => [
                    'Spam' => 'spam',
                    'Contenu offensant' => 'offensive_content',
                    'Harcèlement' => 'harassment',
                    'Autre' => 'other',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommentReport::class,
        ]);
    }
}