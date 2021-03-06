<?php

namespace Serenata\Tests\Unit\Indexing;

use DateTime;

use Serenata\Indexing\Structures;
use Serenata\Indexing\IndexFilePruner;
use Serenata\Indexing\StorageInterface;
use Serenata\Indexing\FileExistenceCheckerInterface;

use Serenata\Tests\Integration\AbstractIntegrationTest;

final class FilePruningTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testDoesNothingWhenThereIsNothingToPrune(): void
    {
        $storage = $this->getMockBuilder(StorageInterface::class)->getMock();
        $fileExistenceChecker = $this->getMockBuilder(FileExistenceCheckerInterface::class)->getMock();

        $storage->expects(self::once())->method('getFiles')->willReturn([
            new Structures\File('file:///testPath.php', new DateTime(), []),
        ]);

        $pruner = new IndexFilePruner($storage, $fileExistenceChecker);

        $fileExistenceChecker->expects(self::once())->method('exists')->with('file:///testPath.php')->willReturn(true);
        $storage->expects(self::never())->method('delete');

        $pruner->prune();
    }

    /**
     * @return void
     */
    public function testPrunesFileThatNoLongerExists(): void
    {
        $storage = $this->getMockBuilder(StorageInterface::class)->getMock();
        $fileExistenceChecker = $this->getMockBuilder(FileExistenceCheckerInterface::class)->getMock();

        $storage->expects(self::once())->method('getFiles')->willReturn([
            new Structures\File('file:///testPath.php', new DateTime(), []),
        ]);

        $pruner = new IndexFilePruner($storage, $fileExistenceChecker);

        $fileExistenceChecker->expects(self::once())->method('exists')->with('file:///testPath.php')->willReturn(false);
        $storage->expects(self::once())->method('delete');

        $pruner->prune();
    }
}
