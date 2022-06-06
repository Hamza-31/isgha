<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,['attr'=>[
                'class'=>'form-control'],
                'label'=>'Identifiant','constraints'=>[new NotBlank(), new Length(['min'=>'4','max'=>30])]])


            ->add('email', EmailType::class, ['label' => 'E-mail','attr'=>[
                'class'=>'form-control']])
            ->add('phoneNumber', TextType::class, ['label' =>'Numéro de téléphone','attr'=>[
                'class'=>'form-control']])
            ->add('zipcode', TextType::class, ['label' =>'Code Postal','attr'=>[
                'class'=>'form-control']])
            ->add('city', ChoiceType::class, ['label' =>'Ville','attr'=>[
                'class'=>'form-select'],
                'mapped' => false,
                'choices'  => $options['cities'],
                'choice_label' => function (City $city, $key, $value) {
                    return $city->getName();

                }])
            ->add('adress', TextType::class, ['label' =>'Adresse','attr'=>[
                'class'=>'form-control']])

            ->add('plainPassword', RepeatedType::class, [
                'type'=>PasswordType::class,
                'first_options'=>['label'=>'Mot de passe',
                'attr' => ['class'=>'col-md-6 form-control']],
                'second_options'=>['label'=>'Confirmation du mot de passe',
                'attr' => ['class'=>'col-md-6 form-control']],
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label' => 'En m\'inscrivant à ce site j\'accepte les conditions d\'utilisations'
            ])
            ->add('submit',SubmitType::class,['label'=>'S\'inscrire','attr'=>['class'=>'form-control btn btn-primary mt-4']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'cities' => new ArrayCollection()
        ]);
        $resolver->setAllowedTypes('cities', 'array');
    }
}
