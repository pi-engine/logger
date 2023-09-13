<?php

namespace Logger\Middleware;

use Logger\Service\LoggerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Handler\ErrorHandler;

class LoggerMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    /** @var LoggerService */
    protected LoggerService $loggerService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ErrorHandler $errorHandler,
        LoggerService $loggerService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
        $this->loggerService   = $loggerService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Set message
        $message = 'Request Log';

        // Get route information
        $routeMatch = $request->getAttribute('Laminas\Router\RouteMatch');

        // Set log params
        $params = [
            'method'          => $request->getMethod(),
            'uri'             => (string)$request->getUri(),
            'headers'         => $request->getHeaders(),
            'body'            => (string)$request->getBody(),
            'protocolVersion' => $request->getProtocolVersion(),
            'serverParams'    => $request->getServerParams(),
            'queryParams'     => $request->getQueryParams(),
            'parsedBody'      => $request->getParsedBody(),
            'uploadedFiles'   => $request->getUploadedFiles(),
            'cookies'         => $request->getCookieParams(),
            'attributes'      => $request->getAttributes(),
            'target'          => $request->getRequestTarget(),
            'route'           => $routeMatch->getParams(),
        ];

        // Set log
        $this->loggerService->write($message, $params);

        return $handler->handle($request);
    }
}