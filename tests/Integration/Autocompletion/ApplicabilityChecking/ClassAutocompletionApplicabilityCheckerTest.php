<?php

namespace PhpIntegrator\Tests\Integration\Autocompletion\ApplicabilityChecking;

class ClassAutocompletionApplicabilityCheckerTest extends AbstractAutocompletionApplicabilityCheckerTest
{
    /**
     * @inheritDoc
     */
    protected function getFileNameOfFileContainingSuggestionSources(): ?string
    {
        return 'ClassList.phpt';
    }

    /**
     * @inheritDoc
     */
    public function getFileNamesWhereShouldApply(): array
    {
        return [
            'TopLevelNamespace.phpt',
            'FunctionLike.phpt',
            'ParameterType.phpt',
            'New.phpt',
            'ClassExtends.phpt',
            'UseStatement.phpt'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'applicabilityCheckingClassAutocompletionProvider';
    }
}