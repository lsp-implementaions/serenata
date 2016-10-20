<?php

namespace PhpIntegrator\Analysis\Docblock;

use ReflectionClass;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;

use phpDocumentor\Reflection\Types\Context;

/**
 * Extension of the {@see FqsenResolver} from phpDocumentor that does absolutely nothing whilst resolving and returns
 * the input type again.
 *
 * Apparently, phpDocumentor's code insists on resolving all types in a docblock. If no context (i.e. namespace, use
 * statements, ...) for type resolution is provided, it'll create a context all by itself and make everything relative
 * to the "root" namespace (i.e. an FQCN).
 *
 * phpDocumentor's TypeResolver needs an instance of an FqsenResolver and we need the unaltered, original, docblock
 * types, hence this class.
 */
class DummyDocblockFqsenResolver extends FqsenResolver
{
    /**
     * @inheritDoc
     */
    public function resolve($fqsen, Context $context = null)
    {
        /*
            If anyone is looking for a good explanation why Reflection is being used, you'll find it right here.

            This is what you get when you tightly couple your class (\phpDocumentor\Reflection\Types\Object_) to another
            class (phpDocumentor\Reflection\Fqsen), not using an interface, and then declare both classes as final. Its
            reasoning is understandable, given that both classes are value objects. However, in doing so you have taken
            away all extensibility from users (i.e. us) of your library.

            Also, IMHO it is the user's job to extend in a responsible way, i.e. if I decide to mess up the original
            implementation because of poor logic in my inherited class, that is on me. Preventing users from doing this
            feels like not giving anyone a knife because they may cut themselves with it, also preventing anyone that
            can properly use the knife from being able to properly cut their food.

            For my specialized use case, I have no choice but to either hack my way into the class hierarchy using
            Reflection or to cease using the library in its entirety because my use case does not fit exactly within
            its goals. My initial choice went out to the latter if it were not for the library being much better at its
            job than any implementation we had before is.
        */

        $dummyFqsen = new Fqsen('\Dummy');

        // TODO: Use getters that initialize once with class properties to avoid spawning reflection class and
        // properties each time.

        $reflectionClass = new ReflectionClass(Fqsen::class);
        $fqsenReflectionProperty = $reflectionClass->getProperty('fqsen');
        $nameReflectionProperty = $reflectionClass->getProperty('name');

        $fqsenReflectionProperty->setAccessible(true);
        $fqsenReflectionProperty->setValue($dummyFqsen, $fqsen);

        $matches = explode('\\', $fqsen);
        $name = trim(end($matches), '()');

        $nameReflectionProperty->setAccessible(true);
        $nameReflectionProperty->setValue($dummyFqsen, $name);

        return $dummyFqsen;
    }
}
