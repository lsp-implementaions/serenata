<?php

namespace Serenata\UserInterface\Command;

use Serenata\Indexing\StorageInterface;
use Serenata\Indexing\FileIndexerInterface;

use Serenata\Linting\Linter;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Utility\SourceCodeStreamReader;

/**
 * Command that lints a file for various problems.
 */
final class LintCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var Linter
     */
    private $linter;

    /**
     * @var FileIndexerInterface
     */
    private $fileIndexer;

    /**
     * @param StorageInterface       $storage
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     * @param Linter                 $linter
     * @param FileIndexerInterface   $fileIndexer
     */
    public function __construct(
        StorageInterface $storage,
        SourceCodeStreamReader $sourceCodeStreamReader,
        Linter $linter,
        FileIndexerInterface $fileIndexer
    ) {
        $this->storage = $storage;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
        $this->linter = $linter;
        $this->fileIndexer = $fileIndexer;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $arguments = $queueItem->getRequest()->getParams() ?: [];

        if (!isset($arguments['file'])) {
            throw new InvalidArgumentsException('A file name is required for this command.');
        }

        $code = null;

        if (isset($arguments['stdin']) && $arguments['stdin']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']);
        }

        return new JsonRpcResponse(
            $queueItem->getRequest()->getId(),
            $this->lint($arguments['file'], $code)
        );
    }

    /**
     * @param string $filePath
     * @param string $code
     *
     * @return array
     */
    public function lint(string $filePath, string $code): array
    {
        // Not used (yet), but still throws an exception when file is in index.
        $file = $this->storage->getFileByPath($filePath);

        // $this->fileIndexer->index($filePath, $code);

        return $this->linter->lint($code);
    }
}
