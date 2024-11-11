<?php

namespace Logger\Middleware;

use Logger\Service\LoggerService;
use Pi\Core\Handler\ErrorHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggerRequestMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    /** @var LoggerService */
    protected LoggerService $loggerService;

    public string $messageFormat = '%s-%s-%s-%s';

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
        // Get attributes
        $attributes = $request->getAttributes();

        // Get route information
        $routeMatch  = $request->getAttribute('Laminas\Router\RouteMatch');
        $routeParams = $routeMatch->getParams();

        // Set message
        $message = sprintf(
            $this->messageFormat,
            $routeParams['module'],
            $routeParams['section'],
            $routeParams['package'],
            $routeParams['handler']
        );

        // Set log params
        $params = [
            'user_id'         => $attributes['account']['id'] ?? 0,
            'company_id'      => $attributes['company_authorization']['company_id'] ?? 0,
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
            'route'           => $routeParams,
        ];

        // Set log
        $this->loggerService->write($message, $params);

        return $handler->handle($request);
    }
}