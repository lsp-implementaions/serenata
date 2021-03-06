<?php

namespace Serenata\Mediating;

use Evenement\EventEmitterInterface;

use Serenata\Analysis\ConstantListRegistry;

use Serenata\Analysis\Conversion\ConstantConverter;

use Serenata\Indexing\Structures;
use Serenata\Indexing\IndexingEventName;

/**
 * Mediator that updates the constant registry when constant indexing events happen.
 */
final class ConstantIndexingConstantRegistryMediator
{
    /**
     * @var ConstantListRegistry
     */
    private $constantListRegistry;

    /**
     * @var ConstantConverter
     */
    private $constantConverter;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param ConstantListRegistry  $constantListRegistry
     * @param ConstantConverter     $constantConverter
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        ConstantListRegistry $constantListRegistry,
        ConstantConverter $constantConverter,
        EventEmitterInterface $eventEmitter
    ) {
        $this->constantListRegistry = $constantListRegistry;
        $this->constantConverter = $constantConverter;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    private function setup(): void
    {
        $this->eventEmitter->on(IndexingEventName::CONSTANT_UPDATED, function (Structures\Constant $constant): void {
            $this->constantListRegistry->add($this->constantConverter->convert($constant));
        });

        $this->eventEmitter->on(IndexingEventName::CONSTANT_REMOVED, function (Structures\Constant $constant): void {
            $this->constantListRegistry->remove($this->constantConverter->convert($constant));
        });
    }
}
