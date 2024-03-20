<?php

namespace App\Validator;

use App\Service\VideoService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SupportedVideoURLValidator extends ConstraintValidator
{
    public function __construct(private VideoService $videoService)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var string $value Video URL. */
        /** @var SupportedVideoURL $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (false === $this->videoService->isSupported($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
