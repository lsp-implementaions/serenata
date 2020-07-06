<?php

namespace Serenata\Tooltips;

use Serenata\Analysis\Node\PropertyFetchPropertyInfoRetriever;

use Serenata\Common\Position;

use PhpParser\Node;

use Serenata\Utility\TextDocumentItem;

/**
 * Provides tooltips for {@see Node\Expr\StaticPropertyFetch} nodes.
 */
final class StaticPropertyFetchNodeTooltipGenerator
{
    /**
     * @var PropertyFetchPropertyInfoRetriever
     */
    private $propertyFetchPropertyInfoRetriever;

    /**
     * @var PropertyTooltipGenerator
     */
    private $propertyTooltipGenerator;

    /**
     * @param PropertyFetchPropertyInfoRetriever $propertyFetchPropertyInfoRetriever
     * @param PropertyTooltipGenerator           $propertyTooltipGenerator
     */
    public function __construct(
        PropertyFetchPropertyInfoRetriever $propertyFetchPropertyInfoRetriever,
        PropertyTooltipGenerator $propertyTooltipGenerator
    ) {
        $this->propertyFetchPropertyInfoRetriever = $propertyFetchPropertyInfoRetriever;
        $this->propertyTooltipGenerator = $propertyTooltipGenerator;
    }

    /**
     * @param Node\Expr\StaticPropertyFetch $node
     * @param TextDocumentItem              $textDocumentItem
     * @param Position                      $position
     *
     * @throws TooltipGenerationFailedException
     *
     * @return string
     */
    public function generate(
        Node\Expr\StaticPropertyFetch $node,
        TextDocumentItem $textDocumentItem,
        Position $position
    ): string {
        $infoElements = $this->propertyFetchPropertyInfoRetriever->retrieve($node, $textDocumentItem, $position);

        if (count($infoElements) === 0) {
            throw new TooltipGenerationFailedException('No property fetch information was found for node');
        }

        // Fetch the first tooltip. In theory, multiple tooltips are possible, but we don't support these at the moment.
        $info = array_shift($infoElements);

        assert(
            $info !== null,
            'Null should never be returned on shifting as the error was already guaranteed to be not empty'
        );

        return $this->propertyTooltipGenerator->generate($info);
    }
}
