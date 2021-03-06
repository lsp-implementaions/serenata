<?php

namespace Serenata\Tests\Integration\Indexing;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\Utility\TextDocumentItem;

use Serenata\Workspace\ActiveWorkspaceManager;

use Serenata\Workspace\Configuration\WorkspaceConfiguration;

use Serenata\Workspace\Workspace;

final class FileIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testFileTimestampIsUpdatedOnReindexWhenContentChanges(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $code = '<?php class A {}';

        $this->container->get('fileIndexer')->index(new TextDocumentItem($path, $code));

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files);

        $timestamp = $files[0]->getIndexedOn();

        $code = '<?php class B {}';

        $this->container->get('fileIndexer')->index(new TextDocumentItem($path, $code));

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files);
        self::assertTrue($files[0]->getIndexedOn() > $timestamp);
    }

    // TODO: See https://gitlab.com/Serenata/Serenata/issues/278 .
    // /**
    //  * @return void
    //  */
    // public function testUriWithSpacesIsProperlyIndexed(): void
    // {
    //     $path = $this->getPathFor('Folder');
    //
    //     $this->indexTestFile($this->container, $path);
    //
    //     $files = $this->container->get('storage')->getFiles();
    //
    //     self::assertCount(1, $files);
    //     self::assertSame($this->getPathFor('Folder') . '/' . urlencode('Test Spaces.phpt'), $files[0]->getUri());
    // }

    /**
     * @return void
     */
    public function testIndexingIgnoresFilesMatchingExclusionPatterns(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            ['TestFile.phpt'],
            ['php', 'phpt']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(0, $files, 'Files matched by exclusion patterns should not be indexed');
    }

    /**
     * @return void
     */
    public function testIndexesRecursively(): void
    {
        $path = $this->getPathFor('Folder');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            [],
            ['php', 'phpt']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(2, $files, 'Folders must be indexed recursively');
    }

    /**
     * @return void
     */
    public function testIndexingIgnoresFilesInFoldersMatchingExclusionPatterns(): void
    {
        $path = $this->getPathFor('Folder');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            ['Folder'],
            ['php', 'phpt']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(0, $files, 'Folders matched by exclusion patterns should not be indexed');
    }

    /**
     * @return void
     */
    public function testIndexingIgnoresFilesNotMatchingExtensions(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            [],
            ['blah']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(0, $files, 'Files matched not matching specified extensions should not be indexed');
    }

    /**
     * @return void
     */
    public function testIndexingIgnoresAllFilesWhenNoExtensionsAreConfigured(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            [],
            []
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(0, $files, 'Files matched not matching specified extensions should not be indexed');
    }

    /**
     * @return void
     */
    public function testIndexingDoesNotIgnoreFilesThatStartWithDotSuchAsMetaFiles(): void
    {
        $path = $this->getPathFor('.phpstorm.meta.phpt');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            [],
            ['phpt']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files, 'Files that start with a dot such as PhpStorm meta files should be indexed');
    }

    /**
     * @return void
     */
    public function testFileIndexIsSkippedIfSourceDidNotChange(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $code = '<?php class A {}';

        $this->container->get('fileIndexer')->index(new TextDocumentItem($path, $code));

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files);

        $timestamp = $files[0]->getIndexedOn();

        $this->container->get('fileIndexer')->index(new TextDocumentItem($path, $code));

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files);
        self::assertTrue($files[0]->getIndexedOn() > $timestamp);
    }

    /**
     * @return void
     */
    public function testIndexingIsSkippedIfDirectoryDoesNotExist(): void
    {
        $path = $this->getPathFor('FolderThatDoesNotExist');

        $this->container->get(ActiveWorkspaceManager::class)->setActiveWorkspace(new Workspace(new WorkspaceConfiguration(
            [],
            ':memory:',
            7.1,
            [],
            ['blah']
        )));

        $this->indexTestFile($this->container, $path, true);

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(0, $files, 'Files matched not matching specified extensions should not be indexed');
    }

    /**
     * @return void
     */
    public function testSourceHashIsUpdatedOnIndex(): void
    {
        $path = $this->getPathFor('TestFile.phpt');

        $code = '<?php class A {}';

        $this->container->get('fileIndexer')->index(new TextDocumentItem($path, $code));

        $files = $this->container->get('storage')->getFiles();

        self::assertCount(1, $files);
        self::assertNotNull($files[0]->getLastIndexedSourceHash());
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getPathFor(string $file): string
    {
        return $this->normalizePath('file://' . __DIR__ . '/FileIndexingTest/' . $file);
    }
}
