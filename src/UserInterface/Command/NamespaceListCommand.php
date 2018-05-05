<?php

namespace Serenata\UserInterface\Command;

use Serenata\Analysis\NamespaceListProviderInterface;
use Serenata\Analysis\FileNamespaceListProviderInterface;

use Serenata\Indexing\StorageInterface;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

/**
 * Command that shows a list of available namespace.
 */
final class NamespaceListCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var NamespaceListProviderInterface
     */
    private $namespaceListProvider;

    /**
     * @var FileNamespaceListProviderInterface
     */
    private $fileNamespaceListProvider;

    /**
     * @param StorageInterface                   $storage
     * @param NamespaceListProviderInterface     $namespaceListProvider
     * @param FileNamespaceListProviderInterface $fileNamespaceListProvider
     */
    public function __construct(
        StorageInterface $storage,
        NamespaceListProviderInterface $namespaceListProvider,
        FileNamespaceListProviderInterface $fileNamespaceListProvider
    ) {
        $this->storage = $storage;
        $this->namespaceListProvider = $namespaceListProvider;
        $this->fileNamespaceListProvider = $fileNamespaceListProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $arguments = $queueItem->getRequest()->getParams() ?: [];

        return new JsonRpcResponse(
            $queueItem->getRequest()->getId(),
            $this->getNamespaceList($arguments['file'] ?? null)
        );
    }

    /**
     * @param string|null $filePath
     *
     * @return array
     */
    public function getNamespaceList(?string $filePath = null): array
    {
        $criteria = [];

        if ($filePath !== null) {
            $file = $this->storage->getFileByPath($filePath);

            return $this->fileNamespaceListProvider->getAllForFile($file);
        }

        return $this->namespaceListProvider->getAll();
    }
}
