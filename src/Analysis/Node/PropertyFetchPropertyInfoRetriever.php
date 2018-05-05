<?php

namespace Serenata\Analysis\Node;

use AssertionError;
use UnexpectedValueException;

use Serenata\Analysis\ClasslikeInfoBuilderInterface;

use Serenata\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use Serenata\Indexing\Structures;

use PhpParser\Node;

/**
 * Fetches method information from a {@see Node\Expr\PropertyFetch} or a {@see Node\Expr\StaticPropertyFetch} node.
 */
class PropertyFetchPropertyInfoRetriever
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ClasslikeInfoBuilderInterface
     */
    private $classlikeInfoBuilder;

    /**
     * @param NodeTypeDeducerInterface      $nodeTypeDeducer
     * @param ClasslikeInfoBuilderInterface $classlikeInfoBuilder
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer, ClasslikeInfoBuilderInterface $classlikeInfoBuilder)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param Node\Expr\PropertyFetch|Node\Expr\StaticPropertyFetch $node
     * @param Structures\File                                       $file
     * @param string                                                $code
     * @param int                                                   $offset
     *
     * @throws UnexpectedValueException when a dynamic property fetch is passed.
     * @throws UnexpectedValueException when the type the property is fetched from could not be determined.
     *
     * @return array[]
     */
    public function retrieve(Node\Expr $node, Structures\File $file, string $code, int $offset): array
    {
        if ($node->name instanceof Node\Expr) {
            // Can't currently deduce type of an expression such as "$this->{$foo}";
            throw new UnexpectedValueException('Can\'t determine information of dynamic property fetch');
        } elseif (!$node instanceof Node\Expr\PropertyFetch && !$node instanceof Node\Expr\StaticPropertyFetch) {
            throw new AssertionError('Expected property fetch node, got ' . get_class($node) . ' instead');
        }

        $objectNode = ($node instanceof Node\Expr\PropertyFetch) ? $node->var : $node->class;

        $typesOfVar = $this->nodeTypeDeducer->deduce($objectNode, $file, $code, $offset);

        $infoElements = [];

        foreach ($typesOfVar as $type) {
            $info = null;

            try {
                $info = $this->classlikeInfoBuilder->build($type);
            } catch (UnexpectedValueException $e) {
                continue;
            }

            if (!isset($info['properties'][$node->name->name])) {
                continue;
            }

            $infoElements[] = $info['properties'][$node->name->name];
        }

        return $infoElements;
    }
}
