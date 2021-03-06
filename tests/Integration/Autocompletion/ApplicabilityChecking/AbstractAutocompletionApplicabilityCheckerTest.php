<?php

namespace Serenata\Tests\Integration\Autocompletion\ApplicabilityChecking;

use Serenata\Tests\Integration\Autocompletion\Providers\AbstractAutocompletionProviderTest;

/**
 * Abstract base class for autocompletion provider applicability checker integration tests.
 */
abstract class AbstractAutocompletionApplicabilityCheckerTest extends AbstractAutocompletionProviderTest
{
    public function setUp(): void
    {
        parent::setUp();

        $unknownFileNames = array_diff($this->getFileNamesWhereShouldApply(), $this->getFileNamesToEvaluate());

        if ($unknownFileNames !== []) {
            self::fail(
                'Only specify files that are in the list of file names to evaluate. If adding a new file, add it to ' .
                'the base list so it is automatically evaluated for other providers as well. File names to add: ' .
                implode(', ', $unknownFileNames)
            );
        }
    }

    /**
     * @return void
     */
    public function testAppliesAtProvidedLocations(): void
    {
        foreach ($this->getFileNamesWhereShouldApply() as $fileName) {
            self::assertNotEmpty(
                $this->provide($fileName, $this->getFileNameOfFileContainingSuggestionSources()),
                'Autocompletion should apply in "' . $fileName . '", but it doesn\'t'
            );
        }
    }

    /**
     * @return void
     */
    public function testDoesNotApplyAtProvidedLocations(): void
    {
        $fileNamesWhereShouldNotApply = array_diff(
            $this->getFileNamesToEvaluate(),
            $this->getFileNamesWhereShouldApply()
        );

        foreach ($fileNamesWhereShouldNotApply as $fileName) {
            self::assertEmpty(
                $this->provide($fileName, $this->getFileNameOfFileContainingSuggestionSources()),
                'Autocompletion shouldn\'t apply in "' . $fileName . '", but it does'
            );
        }
    }

    /**
     * @return string[]
     */
    abstract protected function getFileNamesWhereShouldApply(): array;

    /**
     * @return string|null
     */
    abstract protected function getFileNameOfFileContainingSuggestionSources(): ?string;

    /**
     * @return string[]
     */
    protected function getFileNamesToEvaluate(): array
    {
        return [
            'AnonymousFunctionBind.phpt',
            'TopLevelNamespace.phpt',
            'FunctionLike.phpt',
            'VariableName.phpt',
            'MethodCall.phpt',
            'ClassConstant.phpt',
            'ClassConstFetch.phpt',
            'ClassConstFetchClassName.phpt',
            'ClassConstFetchNoDelimiter.phpt',
            'Namespace.phpt',
            'UseStatement.phpt',
            'UseStatementFunction.phpt',
            'UseStatementConstant.phpt',
            'Docblock.phpt',
            'DocblockTag.phpt',
            'Comment.phpt',
            'FunctionSignature.phpt',
            'MethodSignature.phpt',
            'Property.phpt',
            'StaticProperty.phpt',
            'PropertyFetch.phpt',
            'ClassBody.phpt',
            'String.phpt',
            'StaticMethodCall.phpt',
            'StaticMethodCallSelf.phpt',
            'StaticMethodCallParent.phpt',
            'StaticMethodCallClassName.phpt',
            'StaticPropertyFetch.phpt',
            'StaticPropertyFetchClassName.phpt',
            'StaticPropertyFetchError.phpt',
            'StaticPropertyFetchSelf.phpt',
            'StaticPropertyFetchSelfError.phpt',
            'StaticPropertyFetchStatic.phpt',
            'StaticPropertyFetchStaticError.phpt',
            'StaticPropertyFetchParent.phpt',
            'StaticPropertyFetchParentError.phpt',
            'ParameterName.phpt',
            'ParameterType.phpt',
            'ParameterDefaultValue.phpt',
            'PropertyType.phpt',
            'Clone.phpt',
            'New.phpt',
            'ClassExtends.phpt',
            'InterfaceExtends.phpt',
            'Implements.phpt',
            'TraitUse.phpt',
            'TraitAlias.phpt',
            'TraitPrecedence.phpt',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return '/../ApplicabilityChecking/ApplicabilityCheckingAutocompletionProviderTest';
    }
}
