<?php

namespace App\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET')
            ->add('isCompleted', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                    'Any' => null
                ]
            ]);
        $builder->add('submit', SubmitType::class);
    }

}