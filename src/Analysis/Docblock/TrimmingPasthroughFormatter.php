<?php

namespace PhpIntegrator\Analysis\Docblock;

use phpDocumentor\Reflection\DocBlock\Tag;

use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;

/**
 * Extension of the {@see PassthroughFormatter} that trims the description before returning it.
 *
 * @see https://github.com/phpDocumentor/ReflectionDocBlock/issues/86
 */
class TrimmingPasthroughFormatter extends PassthroughFormatter
{
    /**
     * @inheritDoc
     */
    public function format(Tag $tag)
    {
        $value = parent::format($tag);

        $value = trim($value);

        return $value;
    }
}
