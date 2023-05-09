<?php

namespace Logger\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Logger\Service\LoggerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface; 

class ListHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var LoggerService */
    protected LoggerService $loggerService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerService $loggerService
    ) {
        $this->responseFactory  = $responseFactory;
        $this->streamFactory    = $streamFactory;
        $this->loggerService = $loggerService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {


        // Set result
        return new JsonResponse(
            [
                'result' => true,
                'data'   => $this->loggerService->getAllLog(),
                'error'  => [],
            ],
        );
    }
}
