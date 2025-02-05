<?php

namespace Jot\HfShield\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Scope extends AbstractAnnotation
{

    public function __construct(
        public string|array $allow = []
    )
    {
    }


}