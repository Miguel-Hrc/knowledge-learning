<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for changing a user's password.
 *
 * This form contains a single 'plainPassword' field, which is a repeated password field
 * to ensure that the user confirms their new password. The field is not mapped to any
 * entity property directly.
 */
class ChangePasswordFormType extends AbstractType
{
    /**
     * Builds the password change form.
     *
     * The form has a single repeated password field with validation constraints:
     * - NotBlank: the password cannot be empty
     * - Length: minimum of 6 characters, maximum of 4096 characters
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options Additional options.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Enter your new password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password must contain at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'New Password',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirm your new password',
                ],
                'invalid_message' => 'The passwords are not the same.',
                'mapped' => false,
            ]);
    }

    /**
     * Configures the options for this form type.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}