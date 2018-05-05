<?php

namespace Serenata\Tests\Integration\Analysis;

use Serenata\Tests\Integration\AbstractIntegrationTest;

/**
 * Contains tests that test whether the registry properly interacts with workspace changes.
 */
class NamespaceListRegistryWorkspaceInteractionTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testRegistryIsClearedWhenWorkspaceChanges(): void
    {
        $registry = $this->container->get('namespaceListProvider.registry');

        static::assertEmpty($registry->getAll());

        $registry->add([
            'id'   => 'foo',
            'fqcn' => '\Test'
        ]);

        static::assertCount(1, $registry->getAll());

        $this->container->get('managerRegistry')->setDatabasePath(':memory:');
        $this->container->get('initializeCommand')->initialize(
            $this->mockJsonRpcResponseSenderInterface(),
            false
        );

        static::assertEmpty($registry->getAll());
    }
}
