<?php

namespace Serenata\Tests\Unit\Autocompletion\Providers;

use Serenata\Autocompletion\SuggestionKind;
use Serenata\Autocompletion\AutocompletionSuggestion;
use Serenata\Autocompletion\AutocompletionPrefixDeterminerInterface;

use Serenata\Autocompletion\Providers\AutocompletionProviderInterface;
use Serenata\Autocompletion\Providers\FuzzyMatchingAutocompletionProvider;

use Serenata\Autocompletion\ApproximateStringMatching\BestStringApproximationDeterminerInterface;

use Serenata\Indexing\Structures;

class FuzzyMatchingAutocompletionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testSortsSuggestionsHigherUpThatHaveBetterScore(): void
    {
        $delegate = $this->getMockBuilder(AutocompletionProviderInterface::class)
            ->setMethods(['provide'])
            ->getMock();

        $prefixDeterminer = $this->getMockBuilder(AutocompletionPrefixDeterminerInterface::class)
            ->setMethods(['determine'])
            ->getMock();

        $bestStringApproximationDeterminer = $this->getMockBuilder(BestStringApproximationDeterminerInterface::class)
            ->setMethods(['determine'])
            ->getMock();

        $suggestions = [
            new AutocompletionSuggestion('test1', SuggestionKind::FUNCTION, 'test', null, 'test', null),
            new AutocompletionSuggestion('test12', SuggestionKind::FUNCTION, 'test', null, 'test', null)
        ];

        $delegate->expects($this->once())->method('provide')->willReturn($suggestions);
        $prefixDeterminer->expects($this->once())->method('determine')->willReturn('test');
        $bestStringApproximationDeterminer->expects($this->once())->method('determine')->willReturn([
            $suggestions[1],
            $suggestions[0]
        ]);

        $provider = new FuzzyMatchingAutocompletionProvider(
            $delegate,
            $prefixDeterminer,
            $bestStringApproximationDeterminer,
            15
        );

        static::assertEquals([
            $suggestions[1],
            $suggestions[0]
        ], $provider->provide($this->getFileStub(), "test", 4));
    }

    /**
     * @return Structures\File
     */
    private function getFileStub(): Structures\File
    {
        return new Structures\File('TestFile.php', new \DateTime(), []);
    }
}
