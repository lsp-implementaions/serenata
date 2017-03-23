<?php

namespace PhpIntegrator\Analysis\Typing\Resolving;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

/**
 * Resolves local types to their FQCN for a file.
 *
 * This is a convenience layer on top of {@see TypeResolver} that accepts a list of namespaces and imports (use
 * statements) for a file and automatically selects the data relevant at the requested line from the list to feed to
 * the underlying resolver.
 */
class FileTypeResolver implements FileTypeResolverInterface
{
    /**
     * @var array
     */
    private $namespaces;

    /**
     * @var array
     */
    private $imports;

    /**
     * @var TypeResolverInterface
     */
    private $typeResolver;

    /**
     * @param TypeResolverInterface $typeResolver
     * @param array {
     *     @var string   $name
     *     @var int      $startLine
     *     @var int|null $endLine
     * } $namespaces
     * @param array {
     *     @var string $name
     *     @var string $alias
     *     @var string $kind
     *     @var int    $line
     * } $imports
     */
    public function __construct(TypeResolverInterface $typeResolver, array $namespaces, array $imports)
    {
        $this->typeResolver = $typeResolver;
        $this->namespaces = $namespaces;
        $this->imports = $imports;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $name, int $line, string $kind = UseStatementKind::TYPE_CLASSLIKE): ?string
    {
        $namespaceFqcn = null;
        $relevantImports = [];

        $namespace = $this->getRelevantNamespaceForLine($line);

        if ($namespace !== null) {
            $namespaceFqcn = $namespace['name'];
        }

        $relevantImports = $this->getRelevantUseStatementsForLine($line);

        return $this->typeResolver->resolve($name, $namespaceFqcn, $relevantImports, $kind);
    }

    /**
     * @param int $line
     *
     * @return array|null
     */
    protected function getRelevantNamespaceForLine(int $line): ?array
    {
        foreach ($this->namespaces as $namespace) {
            if ($this->lineLiesWithinNamespaceRange($line, $namespace)) {
                return $namespace;
            }
        }

        return null;
    }

    /**
     * @param int $line
     *
     * @return array
     */
    protected function getRelevantUseStatementsForLine(int $line): array
    {
        $namespace = $this->getRelevantNamespaceForLine($line);

        $relevantImports = [];

        if ($namespace !== null) {
            $namespaceFqcn = $namespace['name'];

            foreach ($this->imports as $import) {
                if ($import['line'] <= $line && $this->lineLiesWithinNamespaceRange($import['line'], $namespace)) {
                    $relevantImports[] = $import;
                }
            }
        }

        return $relevantImports;
    }

    /**
     * @param int   $line
     * @param array $namespace
     *
     * @return bool
     */
    protected function lineLiesWithinNamespaceRange(int $line, array $namespace): bool
    {
        return (
            $line >= $namespace['startLine'] &&
            ($line <= $namespace['endLine'] || $namespace['endLine'] === null)
        );
    }
}
