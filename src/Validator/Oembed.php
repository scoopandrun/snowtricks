<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted URL is a video supported by Oembed.
 * 
 * @package App\Validator
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Oembed extends Constraint
{
    public function __construct(
        public string $message = 'This URL is not supported.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
