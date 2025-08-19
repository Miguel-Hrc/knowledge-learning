<?php

namespace App\Form;

use App\Document\CourseDocument;
use App\Document\ThemeDocument;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating or editing a CourseDocument entity in MongoDB.
 *
 * This form includes fields for:
 * - title: the course title
 * - theme: associated ThemeDocument entity
 * - price: the course price in EUR
 * - edit_course_id: a hidden field used to store the course ID when editing
 */
class CourseFormTypeMongo extends AbstractType
{
    /**
     * Builds the course form for MongoDB documents.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options Options for the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('theme', DocumentType::class, [
                'class' => ThemeDocument::class,
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
                'attr' => ['step' => '0.01'],
            ]);
    }

    /**
     * Configures the options for this MongoDB form type.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CourseDocument::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}