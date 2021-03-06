<?php

namespace Serenata\Sockets;

use TypeError;
use UnexpectedValueException;

/**
 * A request in JSON-RPC 2.0 format.
 *
 * This class doubles as notification by setting the "ID" to null.
 *
 * Value object.
 */
final class JsonRpcRequest implements JsonRpcMessageInterface
{
    /**
     * @var string
     */
    private $jsonrpc;

    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array<string,mixed>|null
     */
    private $params;

    /**
     * @param string|int|null          $id
     * @param string                   $method
     * @param array<string,mixed>|null $params
     * @param string                   $jsonrpc The version.
     */
    public function __construct($id, string $method, ?array $params = null, string $jsonrpc = '2.0')
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @return string
     */
    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * Alias for {@see getJsonrpc}.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->getJsonrpc();
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array<string,mixed> $array
     *
     * @return static
     */
    public static function createFromArray(array $array)
    {
        try {
            $object = new static(
                array_key_exists('id', $array) ? $array['id'] : null,
                $array['method'],
                $array['params'],
                $array['jsonrpc']
            );
        } catch (TypeError $e) {
            throw new UnexpectedValueException(
                'Specified data does not contain data expected for a JSON-RPC request, the data sent was: "' .
                json_encode($array) .'"',
                0,
                $e
            );
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = [
            'jsonrpc' => $this->getJsonrpc(),
            'method'  => $this->getMethod(),
            'params'  => $this->getParams(),
        ];

        // NOTE: Some client implementations (e.g. atom-languageclient) treat a request with an explicit null value for
        // the ID as an actual request instead of a notification, so omit it completely.
        if ($this->getId() !== null) {
            $data['id'] = $this->getId();
        }

        return $data;
    }
}
