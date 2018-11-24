<?php

namespace Serenata\UserInterface\Command;

use Serenata\Indexing\IndexerInterface;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;
use Serenata\Sockets\JsonRpcResponseSenderInterface;

/**
 * Indexes a URI directly.
 *
 * This command bypasses the ordenary channels (such as didChange and didChangeWatchedFiles) and thus circumvents
 * things such as debouncing.
 *
 * Having a separate command also allows assigning a separate priority to the handling of this event and other commands
 * that indirectly also index URI's.
 *
 * This command should not be invoked from outside the server.
 */
final class IndexCommand extends AbstractCommand
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @param IndexerInterface $indexer
     */
    public function __construct(IndexerInterface $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $parameters = $queueItem->getRequest()->getParams();

        if (!$parameters) {
            throw new InvalidArgumentsException('Missing parameters for index request');
        }

        $this->handle($parameters['textDocument']['uri'], $queueItem->getJsonRpcResponseSender());

        return null; // This is a notification that doesn't expect a response.
    }

    /**
     * @param string                         $uri
     * @param JsonRpcResponseSenderInterface $sender
     */
    public function handle(string $uri, JsonRpcResponseSenderInterface $sender): void
    {
        $this->indexer->index($uri, true, $sender);
    }
}