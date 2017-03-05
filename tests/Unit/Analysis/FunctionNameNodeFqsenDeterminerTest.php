<?php

namespace PhpIntegrator\Tests\Unit\Analysis;

use PhpIntegrator\Analysis\FunctionNameNodeFqsenDeterminer;
use PhpIntegrator\Analysis\GlobalFunctionExistenceCheckerInterface;

use PhpParser\Node;

class FunctionNameNodeFqsenDeterminerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testFullyQualifiedName(): void
    {
        $existenceChecker = $this->getMockBuilder(GlobalFunctionExistenceCheckerInterface::class)
            ->setMethods(['exists'])
            ->getMock();

        $existenceChecker->method('exists')->will($this->returnValue(false));

        $determiner = new FunctionNameNodeFqsenDeterminer($existenceChecker);

        $node = new Node\Name\FullyQualified('\A\foo');
        $node->setAttribute('namespace', null);

        $this->assertEquals('\A\foo', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testQualifiedName(): void
    {
        $existenceChecker = $this->getMockBuilder(GlobalFunctionExistenceCheckerInterface::class)
            ->setMethods(['exists'])
            ->getMock();

        $existenceChecker->method('exists')->will($this->returnValue(false));

        $determiner = new FunctionNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('A\foo');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\N\A\foo', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testUnqualifiedNameThatDoesNotExistRelativeToCurrentNamespace(): void
    {
        $existenceChecker = $this->getMockBuilder(GlobalFunctionExistenceCheckerInterface::class)
            ->setMethods(['exists'])
            ->getMock();

        $existenceChecker->method('exists')->will($this->returnValue(false));

        $determiner = new FunctionNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('foo');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\foo', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testUnqualifiedNameThatExistsRelativeToCurrentNamespace(): void
    {
        $existenceChecker = $this->getMockBuilder(GlobalFunctionExistenceCheckerInterface::class)
            ->setMethods(['exists'])
            ->getMock();

        $existenceChecker->method('exists')->will($this->returnValue(true));

        $determiner = new FunctionNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('foo');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\N\foo', $determiner->determine($node));
    }
}