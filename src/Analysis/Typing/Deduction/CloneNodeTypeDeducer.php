<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\Clone_} node.
 */
class CloneNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    protected $nodeTypeDeducer;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, ?string $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\Clone_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromCloneNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\Clone_ $node
     * @param string|null      $file
     * @param string           $code
     * @param int              $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromCloneNode(Node\Expr\Clone_ $node, ?string $file, string $code, int $offset): array
    {
        return $this->nodeTypeDeducer->deduce($node->expr, $file, $code, $offset);
    }
}
