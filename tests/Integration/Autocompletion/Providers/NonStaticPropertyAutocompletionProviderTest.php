<?php

namespace Serenata\Tests\Integration\Autocompletion\Providers;

use Serenata\Autocompletion\CompletionItemKind;
use Serenata\Autocompletion\CompletionItem;

use Serenata\Common\Range;
use Serenata\Common\Position;

use Serenata\Utility\TextEdit;

final class NonStaticPropertyAutocompletionProviderTest extends AbstractAutocompletionProviderTest
{
    /**
     * @return void
     */
    public function testRetrievesAllProperties(): void
    {
        $fileName = 'Property.phpt';

        $output = $this->provide($fileName);

        $suggestions = [
            new CompletionItem(
                'foo',
                CompletionItemKind::PROPERTY,
                'foo',
                new TextEdit(
                    new Range(
                        new Position(11, 4),
                        new Position(11, 4)
                    ),
                    'foo'
                ),
                'foo',
                null,
                [],
                false,
                'int|string — public — A'
            ),
        ];

        self::assertEquals($suggestions, $output);
    }

    /**
     * @return void
     */
    public function testMarksDeprecatedPropertyAsDeprecated(): void
    {
        $fileName = 'DeprecatedProperty.phpt';

        $output = $this->provide($fileName);

        $suggestions = [
            new CompletionItem(
                'foo',
                CompletionItemKind::PROPERTY,
                'foo',
                new TextEdit(
                    new Range(
                        new Position(11, 4),
                        new Position(11, 4)
                    ),
                    'foo'
                ),
                'foo',
                null,
                [],
                true,
                'mixed — public — A'
            ),
        ];

        self::assertEquals($suggestions, $output);
    }

    /**
     * @return void
     */
    public function testIgnoresGenericTypesForNowAndUsesClasslikeBeforeGenericSyntaxInstead(): void
    {
        $fileName = 'IgnoresGenericTypesForNowAndUsesClasslikeBeforeGenericSyntaxInstead.phpt';

        $output = $this->provide($fileName);

        $suggestions = [
            new CompletionItem(
                'foo',
                CompletionItemKind::PROPERTY,
                'foo',
                new TextEdit(
                    new Range(
                        new Position(9, 4),
                        new Position(9, 4)
                    ),
                    'foo'
                ),
                'foo',
                null,
                [],
                false,
                'string — public — A'
            ),
        ];

        self::assertEquals($suggestions, $output);
    }

    /**
     * @return void
     */
    public function testDoesNotReturnStaticProperty(): void
    {
        $fileName = 'StaticProperty.phpt';

        $output = $this->provide($fileName);

        self::assertEquals([], $output);
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return 'NonStaticPropertyAutocompletionProviderTest';
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'nonStaticPropertyAutocompletionProvider';
    }
}
