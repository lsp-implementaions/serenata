<?php

namespace PhpIntegrator\Analysis\Docblock;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\FqsenResolver;

use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;

/**
 * Factory that creates a {@see StandardTagFactory} instance.
 */
class StandardTagFactoryFactory
{
    /**
     * @param FqsenResolver $fqsenResolver
     * @param TypeResolver  $typeResolver
     *
     * @return TypeResolver
     */
    public function create(FqsenResolver $fqsenResolver, TypeResolver $typeResolver)
    {
        $standardTagFactory = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($standardTagFactory);

        $standardTagFactory->addService($descriptionFactory);
        $standardTagFactory->addService($typeResolver);

        return $standardTagFactory;
    }
}
