<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Lesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Form type for creating or editing a Lesson entity.
 *
 * This form includes fields for:
 * - title: the lesson title
 * - course: associated Course entity
 * - videoFile: optional video file upload
 * - content: textual content of the lesson
 * - price: the lesson price in EUR
 * - edit_lesson_id: hidden field storing lesson ID when editing
 * - form_type: hidden field indicating if the form is for editing or adding
 */
class LessonFormType extends AbstractType
{
    /**
     * Builds the lesson form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Array of options, including 'is_edit' to indicate editing mode
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('title')
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'label' => 'Course',
            ])
            ->add('edit_lesson_id', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Video',
                'required' => false,
                'mapped' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'required' => false,
                'attr' => ['rows' => 30],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => false,
                'scale' => 2,
                'attr' => ['step' => '0.01'],
            ])
            ->add('form_type', HiddenType::class, [
                'mapped' => false,
                'data' => $isEdit ? 'edit' : 'add',
            ]);
    }

    /**
     * Configures the options for this lesson form.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}