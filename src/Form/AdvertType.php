<?php

namespace App\Form;

use App\Entity\Advert;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Positive;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AdvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,['attr'=>[
                  'class'=>'form-control'],
                'label'=>'Titre',
                'label_attr'=>['class'=>'form-label mt-3']])
            ->add('description',TextareaType ::class,['attr'=>[
                'class'=>'form-control'
            ],
                'label'=>'Description','label_attr'=>['class'=>'form-label mt-3']])
            ->add('price', MoneyType::class, [
                'attr'=>[
                    'class'=>'form-control '
                ],
                'label'=>'Prix',
                'label_attr'=>['class'=>'form-label mt-3'],
                'constraints'=>[
                    new Positive(),
                    new LessThan(100000000)
                ]])
           // ->add('createdAt')
           // ->add('isValid')
           // ->add('idUser')
           // ->add('idLocation')
            ->add('city', ChoiceType::class, [
                'attr'=>[
                    'class'=>'form-select'],
                'label'=>'Ville',
               'label_attr'=>['class'=>'form-label mt-3'],
                'mapped' => false,
                'choices'  => $options['cities'],
//                'choice_value' => function (?City $city) {
//                    return $city->getName() ?? 'inconnu';
//                },
                'choice_label' => function (City $choice, $key, $value) {
                    return $choice->getName();

                },
            ])
            ->add('idCategory',EntityType::class, [
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->getName();
                },
                'attr'=>[
                'class'=>'form-select'
            ],
                'label'=>'Catégorie',
                'label_attr'=>['class'=>'form-label mt-3']])
            ->add('imageFile', VichImageType::class, [
                'attr'=>[
                    'class'=>'form-control'],
                'label' => 'Insérez vos images (Fichier JPG)',
                'label_attr'=>['class'=>'form-label mt-3'],

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes

                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Ajouter un fichier JPG valide s\'il vous plait.',
                    ])
                ],
            ])
            ->add('submit',SubmitType::class,['label'=>'Déposer l\'annonce','attr'=>['class'=>'form-control btn btn-primary mt-4']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {


        // you can also define the allowed types, allowed values and
        // any other feature supported by the OptionsResolver component
        $resolver->setDefaults([
            'data_class' => Advert::class,
            'cities' => new ArrayCollection()
        ]);

        $resolver->setAllowedTypes('cities', ['array', ArrayCollection::class]);

    }
}
