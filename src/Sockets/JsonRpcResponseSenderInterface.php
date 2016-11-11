<?php

namespace PhpIntegrator\Sockets;

/**
 * Interface for classes that can send {@see JsonRpcResponse} objects (to a stream, socket, file, ...).
 */
interface JsonRpcResponseSenderInterface
{
    /**
     * @param JsonRpcResponse $response
     */
    public function send(JsonRpcResponse $response);
}
