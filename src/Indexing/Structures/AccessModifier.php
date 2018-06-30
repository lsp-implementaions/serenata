<?php

namespace Serenata\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents an access modifier.
 */
class AccessModifier
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
