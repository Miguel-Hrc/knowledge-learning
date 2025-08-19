<?php

namespace App\Form;

use App\Document\ThemeDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Form type for creating or editing a ThemeDocument in MongoDB.
 *
 * This form contains the following fields:
 * - name: the name of the theme
 * - edit_theme_id: hidden field used for editing, not mapped to the document
 */
class ThemeFormTypeMongo extends AbstractType
{
    /**
     * Builds the ThemeDocument form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Options array, may contain 'is_edit' boolean flag
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
            ])
            ->add('edit_theme_id', HiddenType::class, [
                'mapped' => false,
                'required' => false,
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
            'data_class' => ThemeDocument::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}