<?php

namespace PhpIntegrator\Test\Parsing;

use PhpIntegrator\Parsing\DocblockParser;

class DocblockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParamTagAtEndIsInterpretedCorrectly()
    {
        $parser = new DocblockParser();
        $result = $parser->parse('
            /**
             * @param string $foo Test description.
             */
        ', [DocblockParser::PARAM_TYPE], '');

        $this->assertEquals([
            '$foo' => [
                'type'        => 'string',
                'description' => 'Test description.',
                'isVariadic'  => false,
                'isReference' => false
            ]
        ], $result['params']);
    }

    public function testParamTagWithAtSymbolIsInterpretedCorrectly()
    {
        $parser = new DocblockParser();
        $result = $parser->parse('
            /**
             * @param string $foo Test description with @ sign.
             */
        ', [DocblockParser::PARAM_TYPE], '');

        $this->assertEquals([
            '$foo' => [
                'type'        => 'string',
                'description' => 'Test description with @ sign.',
                'isVariadic'  => false,
                'isReference' => false
            ]
        ], $result['params']);
    }

    public function testCorrectlyProcessesRussianUnicodeSequences()
    {
        $parser = new DocblockParser();
        $result = $parser->parse('/**
     * @param string|null $someString Имя файла пат
     */', [DocblockParser::PARAM_TYPE], '');

        $this->assertEquals([
            '$someString' => [
                'type'        => 'string|null',
                'description' => 'Имя файла пат',
                'isVariadic'  => false,
                'isReference' => false
            ]
        ], $result['params']);
    }

    public function testVarTagDescriptionStopsAtNextTag()
    {
        $parser = new DocblockParser();
        $result = $parser->parse('
            /**
             * @var int
             *
             * @ORM\Column(type="integer")
             */
        ', [DocblockParser::VAR_TYPE], 'someProperty');

        $this->assertEquals([
            '$someProperty' => [
                'type'        => 'int',
                'description' => ''
            ]
        ], $result['var']);
    }

    public function testVarTagInSingleLineCommentIsCorrectlyIdentified()
    {
        $parser = new DocblockParser();
        $result = $parser->parse('
            /** @var int Some description */
        ', [DocblockParser::VAR_TYPE], 'someProperty');

        $this->assertEquals([
            '$someProperty' => [
                'type'        => 'int',
                'description' => 'Some description'
            ]
        ], $result['var']);
    }
}
