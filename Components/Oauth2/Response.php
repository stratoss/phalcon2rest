<?php

namespace Phalcon2Rest\Components\Oauth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface {

    /** @var  Stream $stream */
    private $stream;

    private $response;

    public function __construct(\Phalcon\Http\Response $response)
    {
        $this->response = $response;
    }

    public function getProtocolVersion()
    {
        // TODO: Implement getProtocolVersion() method.
    }

    public function withProtocolVersion($version)
    {
        // TODO: Implement withProtocolVersion() method.
    }

    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }

    public function hasHeader($name)
    {
        // TODO: Implement hasHeader() method.
    }

    public function getHeader($name)
    {
        // TODO: Implement getHeader() method.
    }

    public function getHeaderLine($name)
    {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value)
    {
        $this->response->setHeader($name, $value);
        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader($name)
    {
        // TODO: Implement withoutHeader() method.
    }

    public function getBody()
    {
        $this->stream = new Stream();
        return $this->stream;
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    public function getStatusCode()
    {
        // TODO: Implement getStatusCode() method.
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->response->setStatusCode($code, $reasonPhrase);
        return $this;
    }

    public function getReasonPhrase()
    {
        // TODO: Implement getReasonPhrase() method.
    }

    public function getToken()
    {
        return $this->stream->getToken();
    }
}