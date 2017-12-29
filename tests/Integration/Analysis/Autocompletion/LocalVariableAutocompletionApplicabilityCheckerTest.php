<?php

namespace PhpIntegrator\Tests\Integration\Analysis\Autocompletion;

class LocalVariableAutocompletionApplicabilityCheckerTest extends AbstractAutocompletionProviderTest
{
    /**
     * @dataProvider getFileNamesWhereShouldApply
     *
     * @param string $fileName
     *
     * @return void
     */
    public function testAppliesAtExpectedLocations(string $fileName): void
    {
        static::assertNotEmpty($this->provide($fileName, 'LocalVariableList.phpt', '// <INJECTION>'));
    }

    /**
     * @dataProvider getFileNamesWhereShouldNotApply
     *
     * @param string $fileName
     *
     * @return void
     */
    public function testDoesNotApplyAtExpectedLocations(string $fileName): void
    {
        static::assertEmpty($this->provide($fileName, 'LocalVariableList.phpt'));
    }

    /**
     * @return string[]
     */
    public function getFileNamesWhereShouldApply(): array
    {
        return [
            ['TopLevelNamespace.phpt'],
            ['FunctionLike.phpt'],
            ['VariableName.phpt']
        ];
    }

    /**
     * @return string[]
     */
    public function getFileNamesWhereShouldNotApply(): array
    {
        return [
            ['MethodCall.phpt'],
            ['StaticMethodCall.phpt'],
            ['ClassConstFetch.phpt'],
            ['UseStatement.phpt'],
            // ['Docblock.phpt'],
            // ['Comment.phpt'],
            // ['FunctionSignature.phpt'],
            // ['MethodSignature.phpt'],
            ['PropertyFetch.phpt'],
            ['StaticPropertyFetch.phpt'],
            ['ClassBody.phpt'],
            ['String.phpt'],
            ['StaticMethodCallSelf.phpt'],
            ['StaticMethodCallParent.phpt']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return 'ApplicabilityCheckingAutocompletionProviderTest';
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'applicabilityCheckingLocalVariableAutocompletionProvider';
    }
}
