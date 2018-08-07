<?php

namespace Serenata\Tests\Integration\Autocompletion\Providers;

use Serenata\Autocompletion\SuggestionKind;
use Serenata\Autocompletion\AutocompletionSuggestion;

use Serenata\Common\Range;
use Serenata\Common\Position;

use Serenata\Utility\TextEdit;

class ConstantAutocompletionProviderTest extends AbstractAutocompletionProviderTest
{
    /**
     * @return void
     */
    public function testRetrievesAllConstants(): void
    {
        $output = $this->provide('Constants.phpt');

        $suggestions = [
            new AutocompletionSuggestion(
                'FOO',
                SuggestionKind::CONSTANT,
                'FOO',
                new TextEdit(
                    new Range(
                        new Position(7, 0),
                        new Position(7, 1)
                    ),
                    'FOO'
                ),
                'FOO',
                null,
                [
                    'returnTypes'  => 'int|string',
                ],
                [],
                false
            ),
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
            new AutocompletionSuggestion(
                'FOO',
                SuggestionKind::CONSTANT,
                'FOO',
                new TextEdit(
                    new Range(
                        new Position(7, 0),
                        new Position(7, 1)
                    ),
                    'FOO'
                ),
                'FOO',
                null,
                [
                    'returnTypes'  => 'int',
                ],
                [],
                true
            ),
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
