<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;

/**
 * Node visitor that fetches usages of (global) functions.
 */
class GlobalFunctionUsageFetchingVisitor extends ResolvedNameAttachingVisitor
{
    /**
     * @var Node\Expr\FuncCall[]
     */
    private $globalFunctionCallList = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return;
        }

        $this->globalFunctionCallList[] = $node;
    }

    /**
     * @return Node\Expr\FuncCall[]
     */
    public function getGlobalFunctionCallList(): array
    {
        return $this->globalFunctionCallList;
    }
}
