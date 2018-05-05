<?php

namespace Serenata\Tooltips;

use UnexpectedValueException;

use Serenata\Analysis\Node\MethodCallMethodInfoRetriever;

use Serenata\Indexing\Structures;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Expr\StaticCall} nodes.
 */
class StaticMethodCallNodeTooltipGenerator
{
    /**
     * @var MethodCallMethodInfoRetriever
     */
    private $methodCallMethodInfoRetriever;

    /**
     * @var FunctionTooltipGenerator
     */
    private $functionTooltipGenerator;

    /**
     * @param MethodCallMethodInfoRetriever $methodCallMethodInfoRetriever
     * @param FunctionTooltipGenerator      $functionTooltipGenerator
     */
    public function __construct(
        MethodCallMethodInfoRetriever $methodCallMethodInfoRetriever,
        FunctionTooltipGenerator $functionTooltipGenerator
    ) {
        $this->methodCallMethodInfoRetriever = $methodCallMethodInfoRetriever;
        $this->functionTooltipGenerator = $functionTooltipGenerator;
    }

    /**
     * @param Node\Expr\StaticCall $node
     * @param Structures\File      $file
     * @param string               $code
     * @param int                  $offset
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function generate(Node\Expr\StaticCall $node, Structures\File $file, string $code, int $offset): string
    {
        $infoElements = $this->methodCallMethodInfoRetriever->retrieve($node, $file, $code, $offset);

        if (empty($infoElements)) {
            throw new UnexpectedValueException('No method call information was found for node');
        }

        // Fetch the first tooltip. In theory, multiple tooltips are possible, but we don't support these at the moment.
        $info = array_shift($infoElements);

        return $this->functionTooltipGenerator->generate($info);
    }
}
