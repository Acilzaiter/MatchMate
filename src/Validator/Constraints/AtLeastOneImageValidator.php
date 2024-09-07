<?php

// src/Validator/Constraints/AtLeastOneImageValidator.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AtLeastOneImageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($this->context->getGroup() !== 'create') {
            // Skip validation for update scenario
            return;
        }

        // Validate that at least one image is uploaded
        if ($value === null || count($value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

