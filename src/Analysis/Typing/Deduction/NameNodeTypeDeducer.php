<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;
use PhpIntegrator\Analysis\Typing\FileClassListProviderInterface;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Utility\NodeHelpers;
use PhpIntegrator\Utility\SourceCodeHelpers;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Name} node.
 */
class NameNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @var FileClassListProviderInterface
     */
    private $fileClassListProvider;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param ClasslikeInfoBuilder                       $classlikeInfoBuilder
     * @param FileClassListProviderInterface             $fileClassListProvider
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(
        TypeAnalyzer $typeAnalyzer,
        ClasslikeInfoBuilder $classlikeInfoBuilder,
        FileClassListProviderInterface $fileClassListProvider,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
    ) {
        $this->typeAnalyzer = $typeAnalyzer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
        $this->fileClassListProvider = $fileClassListProvider;
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, string $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Name) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromNameNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Name $node
     * @param string    $file
     * @param string    $code
     * @param int       $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromNameNode(Node\Name $node, string $file, string $code, int $offset): array
    {
        $nameString = NodeHelpers::fetchClassName($node);

        if ($nameString === 'static' || $nameString === 'self') {
            $currentClass = $this->findCurrentClassAt($file, $code, $offset);

            if ($currentClass === null) {
                return [];
            }

            return [$this->typeAnalyzer->getNormalizedFqcn($currentClass)];
        } elseif ($nameString === 'parent') {
            $currentClassName = $this->findCurrentClassAt($file, $code, $offset);

            if (!$currentClassName) {
                return [];
            }

            $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($currentClassName);

            if (!$classInfo || empty($classInfo['parents'])) {
                return [];
            }

            $type = $classInfo['parents'][0];

            return [$this->typeAnalyzer->getNormalizedFqcn($type)];
        }

        $line = SourceCodeHelpers::calculateLineByOffset($code, $offset);

        $filePosition = new FilePosition(
            $file,
            new Position($line, 0)
        );

        $fqcn = $this->structureAwareNameResolverFactory->create($filePosition)->resolve($nameString, $filePosition);

        return [$fqcn];
    }

    /**
     * @param string $file
     * @param string $source
     * @param int    $offset
     *
     * @return string|null
     */
    protected function findCurrentClassAt(string $file, string $source, int $offset): ?string
    {
        $line = SourceCodeHelpers::calculateLineByOffset($source, $offset);

        return $this->findCurrentClassAtLine($file, $source, $line);
    }

    /**
     * @param string $file
     * @param string $source
     * @param int    $line
     *
     * @return string|null
     */
    protected function findCurrentClassAtLine(string $file, string $source, int $line): ?string
    {
        $classes = $this->fileClassListProvider->getAllForFile($file);

        foreach ($classes as $fqcn => $class) {
            if ($line >= $class['startLine'] && $line <= $class['endLine']) {
                return $fqcn;
            }
        }

        return null;
    }
}
