<?php

namespace PhpIntegrator\Tests\Integration\Analysis\Autocompletion;

use PhpIntegrator\Analysis\Autocompletion\SuggestionKind;
use PhpIntegrator\Analysis\Autocompletion\AutocompletionSuggestion;

class NamespaceAutocompletionProviderTest extends AbstractAutocompletionProviderTest
{
    /**
     * @return void
     */
    public function testRetrievesAllClasslikes(): void
    {
        $output = $this->provide('Namespace.phpt');

        $suggestions = [
            new AutocompletionSuggestion('Foo', SuggestionKind::IMPORT, 'Foo', 'Foo', null, [
                'isDeprecated' => false,
                'returnTypes'  => 'namespace'
            ])
        ];

        static::assertEquals($suggestions, $output);
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return 'NamespaceAutocompletionProviderTest';
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'namespaceAutocompletionProvider';
    }
}
