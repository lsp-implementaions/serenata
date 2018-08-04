<?php

namespace Serenata\Analysis\Node;

use UnexpectedValueException;

use PhpParser\Node;

use Serenata\Analysis\Visiting\UseStatementKind;

use Serenata\Common\Position;
use Serenata\Common\FilePosition;

use Serenata\Indexing\Structures;

use Serenata\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use Serenata\Utility\NodeHelpers;

/**
 * Determines the FQSEN of a function call node.
 */
class FunctionCallNodeFqsenDeterminer
{
    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory)
    {
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @param Node\Expr\FuncCall $node
     * @param Structures\File    $file
     * @param Position           $position
     *
     * @return string
     */
    public function determine(Node\Expr\FuncCall $node, Structures\File $file, Position $position): string
    {
        $filePosition = new FilePosition($file->getPath(), $position);

        $fileTypeResolver = $this->structureAwareNameResolverFactory->create($filePosition);

        if ($node->name instanceof Node\Expr) {
            // Can't currently deduce type of an expression such as "$this->{$foo}()";
            throw new UnexpectedValueException('Can\'t determine information of dynamic property fetch');
        }

        return $fileTypeResolver->resolve(
            NodeHelpers::fetchClassName($node->name),
            $filePosition,
            UseStatementKind::TYPE_FUNCTION
        );
    }
}
