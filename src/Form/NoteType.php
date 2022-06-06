<?php

namespace App\Form;

use App\Entity\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class,['placeholder' => 'Sélectionner une note','choices'=>['1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5],'attr'=>['class'=>'form-select'],])
            ->add('comment', TextareaType::class,['label'=>'Commentaire (Facultatif)','required'=>false,'label_attr'=>['class'=>'form-label mt-4'],'attr'=>['class'=>'form-control mt-3']])
            //->add('user')
            ->add('submit', SubmitType::class,['label'=>'Soumettre','attr'=>['class'=>'btn btn-primary mt-4']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
