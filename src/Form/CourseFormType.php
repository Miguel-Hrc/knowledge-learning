<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating or editing a Course entity.
 *
 * This form includes fields for:
 * - title: the course title
 * - theme: associated Theme entity
 * - price: the course price in EUR
 * - edit_course_id: a hidden field used to store the course ID when editing
 * - form_type: a hidden field to indicate if the form is in "add" or "edit" mode
 */
class CourseFormType extends AbstractType
{
    /**
     * Builds the course form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options Options for the form, including:
     *                       - is_edit (bool): whether the form is in edit mode.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('title')
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'label' => 'Theme',
            ])
            ->add('edit_course_id', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price', 
                'currency' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.01'
                ],
            ])
            ->add('form_type', HiddenType::class, [
                'mapped' => false,
                'data' => $isEdit ? 'edit' : 'add',
            ]);    
    }

    /**
     * Configures the options for this form type.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}