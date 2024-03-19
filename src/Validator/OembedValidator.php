<?php

namespace App\Validator;

use App\Service\VideoService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OembedValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var Oembed $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $videoService = new VideoService($value);

        if (false === $videoService->isVideo()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
