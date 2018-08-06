<?php

namespace Serenata\Analysis\Node;

use LogicException;
use UnexpectedValueException;

use Serenata\Analysis\FunctionListProviderInterface;

use PhpParser\Node;

use Serenata\Common\Position;

use Serenata\Indexing\Structures;

use Serenata\Utility\PositionEncoding;
use Serenata\Utility\TextDocumentItem;

/**
 * Fetches method information from a {@see Node\Expr\FuncCall} or a {@see Node\Stmt\Function_} node.
 */
class FunctionFunctionInfoRetriever
{
    /**
     * @var FunctionCallNodeFqsenDeterminer
     */
    private $functionCallNodeFqsenDeterminer;

    /**
     * @var FunctionListProviderInterface
     */
    private $functionListProvider;

    /**
     * @param FunctionCallNodeFqsenDeterminer $functionCallNodeFqsenDeterminer
     * @param FunctionListProviderInterface   $functionListProvider
     */
    public function __construct(
        FunctionCallNodeFqsenDeterminer $functionCallNodeFqsenDeterminer,
        FunctionListProviderInterface $functionListProvider
    ) {
        $this->functionCallNodeFqsenDeterminer = $functionCallNodeFqsenDeterminer;
        $this->functionListProvider = $functionListProvider;
    }

    /**
     * @param Node\Expr\FuncCall|Node\Stmt\Function_ $node
     * @param TextDocumentItem                       $textDocumentItem
     * @param Position                               $position
     *
     * @throws UnexpectedValueException when the function wasn't found.
     *
     * @return array
     */
    public function retrieve(Node $node, TextDocumentItem $textDocumentItem, Position $position): array
    {
        if ($node instanceof Node\Stmt\Function_) {
            return $this->getFunctionInfo('\\' . $node->namespacedName->toString());
        } elseif (!$node instanceof Node\Expr\FuncCall) {
            throw new LogicException(
                'Expected function definition node or function call node, got ' . get_class($node) . ' instead'
            );
        } elseif ($node->name instanceof Node\Expr) {
            throw new UnexpectedValueException(
                'Determining the info for dynamic function calls is currently not supported'
            );
        }

        $nameNode = new Node\Name\Relative((string) $node->name);
        $nameNode->setAttribute('namespace', $node->getAttribute('namespace'));

        $fqsen = $this->functionCallNodeFqsenDeterminer->determine($node, $textDocumentItem->getUri(), $position);

        return $this->getFunctionInfo($fqsen);
    }

    /**
     * @param string $fullyQualifiedName
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    private function getFunctionInfo(string $fullyQualifiedName): array
    {
        $functions = $this->functionListProvider->getAll();

        if (!isset($functions[$fullyQualifiedName])) {
            throw new UnexpectedValueException('No data found for function with name ' . $fullyQualifiedName);
        }

        return $functions[$fullyQualifiedName];
    }
}
