<?php

namespace PhpIntegrator\Linting;

/**
 * Interface for analyzers.
 */
interface AnalyzerInterface
{
    /**
     * Retrieves a list of visitors to attach.
     *
     * @return \PhpParser\NodeVisitor[]
     */
    public function getVisitors(): array;

    /**
     * Retrieves a list of problems found during traversal. When this method is called, the visitors have already
     * traversed the code.
     *
     * @return array
     */
    public function getOutput(): array;
}