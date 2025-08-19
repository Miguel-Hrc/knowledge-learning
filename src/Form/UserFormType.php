<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Form type for creating or editing a User entity.
 *
 * This form includes the following fields:
 * - email: the user's email address
 * - password: optional password field for editing or creating users
 * - roles: multiple choice field for selecting user roles
 * - isVerified: checkbox to indicate if the user's email is verified
 * - edit_id: hidden field used for editing, not mapped to the entity
 * - form_type: hidden field indicating whether the form is in "add" or "edit" mode
 */
class UserFormType extends AbstractType
{
    /**
     * Builds the User form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Options array, may contain 'is_edit' boolean flag
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail address',
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Password',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Verified email',
                'required' => false,
            ])
            ->add('edit_id', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('form_type', HiddenType::class, [
                'mapped' => false,
                'data' => $isEdit ? 'edit' : 'add',
            ]);
    }

    /**
     * Configures the options for this form.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}