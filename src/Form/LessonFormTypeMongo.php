<?php

namespace App\Form;

use App\Document\LessonDocument;
use App\Document\CourseDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

/**
 * Form type for creating or editing a LessonDocument in MongoDB.
 *
 * This form includes fields for:
 * - title: the lesson title
 * - course: associated CourseDocument
 * - videoFile: optional video file upload
 * - content: textual content of the lesson
 * - price: the lesson price in EUR
 * - edit_lesson_id: hidden field storing lesson ID when editing
 */
class LessonFormTypeMongo extends AbstractType
{
    /**
     * Builds the lesson form for MongoDB documents.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Array of options, including 'is_edit' to indicate editing mode
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('course', DocumentType::class, [
                'class' => CourseDocument::class,
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
            ]);
    }

    /**
     * Configures the options for this MongoDB lesson form.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LessonDocument::class,
            'is_edit' => false,
            'em' => null,
        ]);
    }
}