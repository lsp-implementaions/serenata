<?php

namespace Serenata\UserInterface\JsonRpcQueueItemHandler;

use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

use Serenata\Analysis\Typing\Deduction\ExpressionTypeDeducer;

use Serenata\Common\Position;

use Serenata\Parsing\ToplevelTypeExtractorInterface;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Utility\TextDocumentItem;
use Serenata\Utility\SourceCodeStreamReader;

/**
 * Allows deducing the types of an expression (e.g. a call chain, a simple string, ...).
 *
 * @deprecated Will be removed as soon as all functionality this facilitates is implemented as LSP-compliant requests.
 */
final class DeduceTypesJsonRpcQueueItemHandler extends AbstractJsonRpcQueueItemHandler
{
    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var ExpressionTypeDeducer
     */
    private $expressionTypeDeducer;

    /**
     * @var ToplevelTypeExtractorInterface
     */
    private $toplevelTypeExtractor;

    /**
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     * @param ExpressionTypeDeducer  $expressionTypeDeducer
     * @param ToplevelTypeExtractorInterface $toplevelTypeExtractor
     */
    public function __construct(
        SourceCodeStreamReader $sourceCodeStreamReader,
        ExpressionTypeDeducer $expressionTypeDeducer,
        ToplevelTypeExtractorInterface $toplevelTypeExtractor
    ) {
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
        $this->expressionTypeDeducer = $expressionTypeDeducer;
        $this->toplevelTypeExtractor = $toplevelTypeExtractor;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ExtendedPromiseInterface
    {
        $arguments = $queueItem->getRequest()->getParams() !== null ?
            $queueItem->getRequest()->getParams() :
            [];

        if (!isset($arguments['uri'])) {
            throw new InvalidArgumentsException('"uri" must be supplied');
        } elseif (!isset($arguments['position'])) {
            throw new InvalidArgumentsException('"position" into the source must be supplied');
        }

        if (isset($arguments['stdin']) && $arguments['stdin'] !== '') {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['uri']);
        }

        $position = new Position($arguments['position']['line'], $arguments['position']['character']);

        $codeWithExpression = $code;

        if (isset($arguments['expression'])) {
            $codeWithExpression = $arguments['expression'];
        }

        $result = $this->deduceTypes(
            $arguments['uri'],
            $code,
            $codeWithExpression,
            $position,
            isset($arguments['ignore-last-element']) && $arguments['ignore-last-element'] === true
        );

        $deferred = new Deferred();
        $deferred->resolve(new JsonRpcResponse($queueItem->getRequest()->getId(), $result));

        return $deferred->promise();
    }

    /**
     * @param string   $uri
     * @param string   $code
     * @param string   $codeWithExpression
     * @param Position $position
     * @param bool     $ignoreLastElement
     *
     * @return string[]
     */
    public function deduceTypes(
        string $uri,
        string $code,
        string $codeWithExpression,
        Position $position,
        bool $ignoreLastElement
    ): array {
        return $this->toplevelTypeExtractor->extract($this->expressionTypeDeducer->deduce(
            new TextDocumentItem($uri, $code),
            $position,
            $codeWithExpression,
            $ignoreLastElement
        ));
    }
}
