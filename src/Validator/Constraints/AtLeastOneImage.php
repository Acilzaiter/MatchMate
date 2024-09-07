<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AtLeastOneImage extends Constraint
{
    public string $message = 'Please upload at least one image.';
}
