<?php

namespace PhpIntegrator\Tests\Integration\Analysis\Autocompletion;

use PhpIntegrator\Analysis\Autocompletion\SuggestionKind;
use PhpIntegrator\Analysis\Autocompletion\AutocompletionSuggestion;

class ConstantAutocompletionProviderTest extends AbstractAutocompletionProviderTest
{
    /**
     * @return void
     */
    public function testRetrievesAllConstants(): void
    {
        $output = $this->provide('Constants.phpt');

        $suggestions = [
            new AutocompletionSuggestion('FOO', SuggestionKind::CONSTANT, 'FOO', 'FOO', null, [
                'isDeprecated'                  => false,
                'url'                           => null,
                'returnTypes'                   => 'int|string'
            ])
        ];

        static::assertEquals($suggestions, $output);
    }

    /**
     * @return void
     */
    public function testMarksDeprecatedConstantAsDeprecated(): void
    {
        $output = $this->provide('DeprecatedConstant.phpt');

        $suggestions = [
            new AutocompletionSuggestion('FOO', SuggestionKind::CONSTANT, 'FOO', 'FOO', null, [
                'isDeprecated'                  => true,
                'url'                           => null,
                'returnTypes'                   => 'int'
            ])
        ];

        static::assertEquals($suggestions, $output);
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return 'ConstantAutocompletionProviderTest';
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'constantAutocompletionProvider';
    }
}