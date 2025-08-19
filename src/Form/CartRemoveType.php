<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for removing an item from the shopping cart.
 *
 * This form does not contain any input fields. It is primarily used to
 * generate a CSRF-protected token for safely submitting a "remove from cart" action.
 */
class CartRemoveType extends AbstractType
{
    /**
     * Builds the form.
     *
     * Since this form does not have any input fields, this method is intentionally left empty.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options Additional options.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // No fields needed for this form; only CSRF protection is used
    }

    /**
     * Configures the options for this form.
     *
     * Enables CSRF protection and sets the CSRF token ID and field name
     * to secure the remove-from-cart action.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'cart_remove',
        ]);
    }
}