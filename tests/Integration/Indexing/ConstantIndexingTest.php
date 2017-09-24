<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConstantIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleConstant(): void
    {
        $constant = $this->indexConstant('SimpleConstant.phpt');

        $this->assertSame('CONSTANT', $constant->getName());
        $this->assertSame('\CONSTANT', $constant->getFqcn());
        $this->assertSame($this->getPathFor('SimpleConstant.phpt'), $constant->getFile()->getPath());
        $this->assertSame(3, $constant->getStartLine());
        $this->assertSame(3, $constant->getEndLine());
        $this->assertSame("'test'", $constant->getDefaultValue());
        $this->assertFalse($constant->getIsDeprecated());
        $this->assertFalse($constant->getHasDocblock());
        $this->assertNull($constant->getShortDescription());
        $this->assertNull($constant->getLongDescription());
        $this->assertNull($constant->getTypeDescription());
        $this->assertCount(1, $constant->getTypes());
        $this->assertSame('string', $constant->getTypes()[0]->getType());
        $this->assertSame('string', $constant->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testDeprecatedConstant(): void
    {
        $constant = $this->indexConstant('DeprecatedConstant.phpt');

        $this->assertTrue($constant->getIsDeprecated());
    }

    /**
     * @return void
     */
    public function testConstantShortDescription(): void
    {
        $constant = $this->indexConstant('ConstantShortDescription.phpt');

        $this->assertSame('This is a summary.', $constant->getShortDescription());
    }

    /**
     * @return void
     */
    public function testConstantLongDescription(): void
    {
        $constant = $this->indexConstant('ConstantLongDescription.phpt');

        $this->assertSame('This is a long description.', $constant->getLongDescription());
    }

    /**
     * @return void
     */
    public function testConstantTypeDescription(): void
    {
        $constant = $this->indexConstant('ConstantTypeDescription.phpt');

        $this->assertSame('This is a type description.', $constant->getTypeDescription());
    }

    /**
     * @return void
     */
    public function testConstantTypeDescriptionIsUsedAsSummaryIfSummaryIsMissing(): void
    {
        $constant = $this->indexConstant('ConstantTypeDescriptionAsSummary.phpt');

        $this->assertSame('This is a type description.', $constant->getShortDescription());
    }

    /**
     * @return void
     */
    public function testConstantTypeDescriptionTakesPrecedenceOverSummary(): void
    {
        $property = $this->indexConstant('ConstantTypeDescriptionTakesPrecedenceOverSummary.phpt');

        $this->assertSame('This is a type description.', $property->getShortDescription());
    }

    /**
     * @return void
     */
    public function testConstantTypeIsFetchedFromDocblockAndGetsPrecedenceOverDefaultValueType(): void
    {
        $constant = $this->indexConstant('ConstantTypeFromDocblock.phpt');

        $this->assertSame('int', $constant->getTypes()[0]->getType());
        $this->assertSame('int', $constant->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testConstantFqcnIsInCurrentNamespace(): void
    {
        $constant = $this->indexConstant('ConstantFqcnInNamespace.phpt');

        $this->assertSame('\A\CONSTANT', $constant->getFqcn());
    }

    /**
     * @return void
     */
    public function testConstantTypeInDocblockIsResolved(): void
    {
        $constant = $this->indexConstant('ConstantTypeInDocblockIsResolved.phpt');

        $this->assertSame('A', $constant->getTypes()[0]->getType());
        $this->assertSame('\N\A', $constant->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

            $this->assertCount(1, $constants);
            $this->assertSame('\CONSTANT', $constants[0]->getFqcn());

            return str_replace('CONSTANT', 'CONSTANT2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

            $this->assertCount(1, $constants);
            $this->assertSame('\CONSTANT2', $constants[0]->getFqcn());
        };

        $path = $this->getPathFor('ConstantChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Constant
     */
    protected function indexConstant(string $file): Structures\Constant
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

        $this->assertCount(1, $constants);

        return $constants[0];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/ConstantIndexingTest/' . $file;
    }
}
