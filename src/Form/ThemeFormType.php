<?php

namespace App\Form;

use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Form type for creating or editing a Theme entity.
 *
 * This form contains the following fields:
 * - name: the name of the theme
 * - edit_theme_id: hidden field used for editing, not mapped to the entity
 * - form_type: hidden field indicating whether the form is used for adding or editing
 */
class ThemeFormType extends AbstractType
{
    /**
     * Builds the Theme form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Options array, may contain 'is_edit' boolean flag
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
            ])
            ->add('edit_theme_id', HiddenType::class, [
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
            'data_class' => Theme::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}