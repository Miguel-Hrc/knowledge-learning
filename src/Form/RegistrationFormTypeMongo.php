<?php

namespace App\Form;

use App\Document\UserDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form type for user registration (MongoDB version).
 *
 * This form handles the registration of a new UserDocument and includes fields for:
 * - email: the user's email address
 * - plainPassword: password entered twice for confirmation
 */
class RegistrationFormTypeMongo extends AbstractType
{
    /**
     * Builds the MongoDB registration form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => "Enter your password",
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password must contain at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'first_options' => [
                    'label' => 'Password'
                ],
                'second_options' => [
                    'label' => 'Confirm your password'
                ]
            ]);
    }

    /**
     * Configures the options for this registration form.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDocument::class,
        ]);
    }

    /**
     * Returns the prefix used for the names of form fields in HTML.
     *
     * @return string The block prefix
     */
    public function getBlockPrefix(): string
    {
        return 'registration_form_mongo';
    }
}