<?php

namespace PhpIntegrator\GotoDefinition;

use LogicException;
use UnexpectedValueException;

use PhpIntegrator\Analysis\Visiting\NodeFetchingVisitor;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

/**
 * Locates the definition of structural elements.
 */
class DefinitionLocator
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FuncCallNodeDefinitionLocator
     */
    private $funcCallNodeDefinitionLocator;

    /**
     * @var MethodCallNodeDefinitionLocator
     */
    private $methodCallNodeDefinitionLocator;

    /**
     * @var ConstFetchNodeDefinitionLocator
     */
    private $constFetchNodeDefinitionLocator;

    /**
     * @var ClassConstFetchNodeDefinitionLocator
     */
    private $classConstFetchNodeDefinitionLocator;

    /**
     * @var NameNodeDefinitionLocator
     */
    private $nameNodeDefinitionLocator;

    /**
     * @param Parser                               $parser
     * @param FuncCallNodeDefinitionLocator        $funcCallNodeDefinitionLocator
     * @param MethodCallNodeDefinitionLocator      $methodCallNodeDefinitionLocator
     * @param ConstFetchNodeDefinitionLocator      $constFetchNodeDefinitionLocator
     * @param ClassConstFetchNodeDefinitionLocator $classConstFetchNodeDefinitionLocator
     * @param NameNodeDefinitionLocator            $nameNodeDefinitionLocator
     */
    public function __construct(
        Parser $parser,
        FuncCallNodeDefinitionLocator $funcCallNodeDefinitionLocator,
        MethodCallNodeDefinitionLocator $methodCallNodeDefinitionLocator,
        ConstFetchNodeDefinitionLocator $constFetchNodeDefinitionLocator,
        ClassConstFetchNodeDefinitionLocator $classConstFetchNodeDefinitionLocator,
        NameNodeDefinitionLocator $nameNodeDefinitionLocator
    ) {
        $this->parser = $parser;
        $this->funcCallNodeDefinitionLocator = $funcCallNodeDefinitionLocator;
        $this->methodCallNodeDefinitionLocator = $methodCallNodeDefinitionLocator;
        $this->constFetchNodeDefinitionLocator = $constFetchNodeDefinitionLocator;
        $this->classConstFetchNodeDefinitionLocator = $classConstFetchNodeDefinitionLocator;
        $this->nameNodeDefinitionLocator = $nameNodeDefinitionLocator;
}

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $position The position to analyze and show the tooltip for (byte offset).
     *
     * @return GotoDefinitionResult|null
     */
    public function locate(Structures\File $file, string $code, int $position): ?GotoDefinitionResult
    {
        $nodes = [];

        try {
            $nodes = $this->getNodesFromCode($code);
            $node = $this->getNodeAt($nodes, $position);

            return $this->locateDefinitionOfStructuralElementRepresentedByNode($node, $file, $code);
        } catch (UnexpectedValueException $e) {
            return null;
        }
    }

    /**
     * @param array $nodes
     * @param int   $position
     *
     * @throws UnexpectedValueException
     *
     * @return Node
     */
    protected function getNodeAt(array $nodes, int $position): Node
    {
        $visitor = new NodeFetchingVisitor($position);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        $node = $visitor->getNode();
        $nearestInterestingNode = $visitor->getNearestInterestingNode();

        if (!$node) {
            throw new UnexpectedValueException('No node found at location ' . $position);
        }

        if ($nearestInterestingNode instanceof Node\Expr\FuncCall ||
            $nearestInterestingNode instanceof Node\Expr\ConstFetch ||
            $nearestInterestingNode instanceof Node\Stmt\UseUse
        ) {
            return $nearestInterestingNode;
        }

        return ($node instanceof Node\Name || $node instanceof Node\Identifier) ? $node : $nearestInterestingNode;
    }

    /**
     * @param Node            $node
     * @param Structures\File $file
     * @param string          $code
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfStructuralElementRepresentedByNode(
        Node $node,
        Structures\File $file,
        string $code
    ): GotoDefinitionResult {
        if ($node instanceof Node\Expr\FuncCall) {
            return $this->locateDefinitionOfFuncCallNode($node);
        } elseif ($node instanceof Node\Expr\ConstFetch) {
            return $this->locateDefinitionOfConstFetchNode($node);
        } elseif ($node instanceof Node\Stmt\UseUse) {
            return $this->locateDefinitionOfUseUseNode($node, $file, $node->getAttribute('startLine'));
        } elseif ($node instanceof Node\Name) {
            return $this->locateDefinitionOfNameNode($node, $file, $node->getAttribute('startLine'));
        } elseif ($node instanceof Node\Identifier) {
            $parentNode = $node->getAttribute('parent', false);

            if ($parentNode === false) {
                throw new LogicException('No parent metadata attached to node');
            }

            /*if ($parentNode instanceof Node\Stmt\Function_) {
                return $this->getTooltipForFunctionNode($parentNode);
            } elseif ($parentNode instanceof Node\Stmt\ClassMethod) {
                return $this->getTooltipForClassMethodNode($parentNode, $file);
            } else*/if ($parentNode instanceof Node\Expr\ClassConstFetch) {
                return $this->locateDefinitionOfClassConstFetchNode($parentNode, $file, $code);
            } /*elseif ($parentNode instanceof Node\Expr\PropertyFetch) {
                return $this->getTooltipForPropertyFetchNode(
                    $parentNode,
                    $file,
                    $code,
                    $parentNode->getAttribute('startFilePos')
                );
            } elseif ($parentNode instanceof Node\Expr\StaticPropertyFetch) {
                return $this->getTooltipForStaticPropertyFetchNode(
                    $parentNode,
                    $file,
                    $code,
                    $parentNode->getAttribute('startFilePos')
                );
            } else*/if ($parentNode instanceof Node\Expr\MethodCall) {
                return $this->locateDefinitionOfMethodCallNode(
                    $parentNode,
                    $file,
                    $code,
                    $parentNode->getAttribute('startFilePos')
                );
            } /*elseif ($parentNode instanceof Node\Expr\StaticCall) {
                return $this->getTooltipForStaticMethodCallNode(
                    $parentNode,
                    $file,
                    $code,
                    $parentNode->getAttribute('startFilePos')
                );
            }*/
        }

        throw new UnexpectedValueException('Don\'t know how to handle node of type ' . get_class($node));
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfFuncCallNode(Node\Expr\FuncCall $node): GotoDefinitionResult
    {
        return $this->funcCallNodeDefinitionLocator->locate($node);
    }

    /**
     * @param Node\Expr\MethodCall $node
     * @param Structures\File      $file
     * @param string               $code
     * @param int                  $offset
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfMethodCallNode(
        Node\Expr\MethodCall $node,
        Structures\File $file,
        string $code,
        int $offset
    ): GotoDefinitionResult {
        return $this->methodCallNodeDefinitionLocator->locate($node, $file, $code, $offset);
    }

    // /**
    //  * @param Node\Expr\StaticCall $node
    //  * @param Structures\File      $file
    //  * @param string               $code
    //  * @param int                  $offset
    //  *
    //  * @throws UnexpectedValueException
    //  *
    //  * @return string
    //  */
    // protected function getTooltipForStaticMethodCallNode(
    //     Node\Expr\StaticCall $node,
    //     Structures\File $file,
    //     string $code,
    //     int $offset
    // ): string {
    //     return $this->staticMethodCallNodeTooltipGenerator->generate($node, $file, $code, $offset);
    // }
    //
    // /**
    //  * @param Node\Expr\PropertyFetch $node
    //  * @param Structures\File         $file
    //  * @param string                  $code
    //  * @param int                     $offset
    //  *
    //  * @throws UnexpectedValueException
    //  *
    //  * @return string
    //  */
    // protected function getTooltipForPropertyFetchNode(
    //     Node\Expr\PropertyFetch $node,
    //     Structures\File $file,
    //     string $code,
    //     int $offset
    // ): string {
    //     return $this->propertyFetchNodeTooltipGenerator->generate($node, $file, $code, $offset);
    // }
    //
    // /**
    //  * @param Node\Expr\StaticPropertyFetch $node
    //  * @param Structures\File               $file
    //  * @param string                        $code
    //  * @param int                           $offset
    //  *
    //  * @throws UnexpectedValueException
    //  *
    //  * @return string
    //  */
    // protected function getTooltipForStaticPropertyFetchNode(
    //     Node\Expr\StaticPropertyFetch $node,
    //     Structures\File $file,
    //     string $code,
    //     int $offset
    // ): string {
    //     return $this->staticPropertyFetchNodeTooltipGenerator->generate($node, $file, $code, $offset);
    // }

    /**
     * @param Node\Expr\ConstFetch $node
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfConstFetchNode(Node\Expr\ConstFetch $node): GotoDefinitionResult
    {
        return $this->constFetchNodeDefinitionLocator->generate($node);
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfClassConstFetchNode(
        Node\Expr\ClassConstFetch $node,
        Structures\File $file,
        string $code
    ): GotoDefinitionResult {
        return $this->classConstFetchNodeDefinitionLocator->locate($node, $file, $code);
    }

    /**
     * @param Node\Stmt\UseUse $node
     * @param Structures\File  $file
     * @param int              $line
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfUseUseNode(
        Node\Stmt\UseUse $node,
        Structures\File $file,
        int $line
    ): GotoDefinitionResult {
        // Use statements are always fully qualified, they aren't resolved.
        $nameNode = new Node\Name\FullyQualified($node->name->toString());

        return $this->nameNodeDefinitionLocator->locate($nameNode, $file, $line);
    }

    // /**
    //  * @param Node\Stmt\Function_ $node
    //  *
    //  * @throws UnexpectedValueException
    //  *
    //  * @return string
    //  */
    // protected function getTooltipForFunctionNode(Node\Stmt\Function_ $node): string
    // {
    //     return $this->functionNodeTooltipGenerator->generate($node);
    // }
    //
    // /**
    //  * @param Node\Stmt\ClassMethod $node
    //  * @param Structures\File       $file
    //  *
    //  * @throws UnexpectedValueException
    //  *
    //  * @return string
    //  */
    // protected function getTooltipForClassMethodNode(Node\Stmt\ClassMethod $node, Structures\File $file): string
    // {
    //     return $this->classMethodNodeTooltipGenerator->generate($node, $file);
    // }

    /**
     * @param Node\Name       $node
     * @param Structures\File $file
     * @param int             $line
     *
     * @throws UnexpectedValueException
     *
     * @return GotoDefinitionResult
     */
    protected function locateDefinitionOfNameNode(
        Node\Name $node,
        Structures\File $file,
        int $line
    ): GotoDefinitionResult {
        return $this->nameNodeDefinitionLocator->locate($node, $file, $line);
    }

    /**
     * @param string $code
     *
     * @throws UnexpectedValueException
     *
     * @return Node[]
     */
    protected function getNodesFromCode(string $code): array
    {
        $nodes = $this->parser->parse($code, $this->getErrorHandler());

        if ($nodes === null) {
            throw new UnexpectedValueException('No nodes returned after parsing code');
        }

        return $nodes;
    }

    /**
     * @return ErrorHandler\Collecting
     */
    protected function getErrorHandler(): ErrorHandler\Collecting
    {
        return new ErrorHandler\Collecting();
    }
}