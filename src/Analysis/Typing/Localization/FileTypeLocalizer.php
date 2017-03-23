<?php

namespace PhpIntegrator\Analysis\Typing\Localization;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

/**
 * Resolves FQCN's back to local types based on use statements and the namespace.
 *
 * This is a convenience layer on top of {@see TypeLocalizer} that accepts a list of namespaces and imports (use
 * statements) for a file and automatically selects the data relevant at the requested line from the list to feed to
 * the underlying localizer.
 */
class FileTypeLocalizer
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
     * @var TypeLocalizer
     */
    private $typeLocalizer;

    /**
     * @param TypeLocalizer $typeLocalizer
     * @param array {
     *     @var string   $fqcn
     *     @var int      $startLine
     *     @var int|null $endLine
     * } $namespaces
     * @param array {
     *     @var string $fqcn
     *     @var string $alias
     *     @var string $kind
     *     @var int    $line
     * } $imports
     */
    public function __construct(TypeLocalizer $typeLocalizer, array $namespaces, array $imports)
    {
        $this->typeLocalizer = $typeLocalizer;
        $this->namespaces = $namespaces;
        $this->imports = $imports;
    }

    /**
     * Resolves and determines the FQCN of the specified type.
     *
     * @param string $name
     * @param int    $line
     * @param string $kind
     *
     * @return string|null
     */
    public function resolve(string $name, int $line, string $kind = UseStatementKind::TYPE_CLASSLIKE): ?string
    {
        $namespaceFqcn = null;
        $relevantImports = [];

        foreach ($this->namespaces as $namespace) {
            if ($this->lineLiesWithinNamespaceRange($line, $namespace)) {
                $namespaceFqcn = $namespace['name'];

                foreach ($this->imports as $import) {
                    if ($import['line'] <= $line && $this->lineLiesWithinNamespaceRange($import['line'], $namespace)) {
                        $relevantImports[] = $import;
                    }
                }

                break;
            }
        }

        return $this->typeLocalizer->localize($name, $namespaceFqcn, $relevantImports, $kind);
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
