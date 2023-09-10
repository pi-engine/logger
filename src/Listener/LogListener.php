<?php

namespace Logger\Listener;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\EventManager\EventInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Db as DbWriter;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\Mvc\MvcEvent;

class LogListener implements BootstrapListenerInterface
{
    public function onBootstrap(MvcEvent|EventInterface $e): void
    {
        // Get the database adapter from the service manager
        $dbAdapter = $e->getApplication()->getServiceManager()->get(AdapterInterface::class);

        // Create a logger object with a database writer
        $writer = new DbWriter($dbAdapter, 'log');
        $logger = new Logger();
        $logger->addWriter($writer);

        // Log the request information
        $request = $e->getRequest();

        // Attach the logger object to the finish event of the application
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_FINISH, function ($e) use ($logger, $request) {
            // Log the response information
            $response = $e->getResponse();
            $logger->info(
                'Response sent',
                [
                    'extra' => json_encode(
                        [
                            'user'     => [],
                            'company'  => [],
                            'request'  => [
                                'method'  => $request->getMethod(),
                                'uri'     => $request->getUriString(),
                                'headers' => $request->getHeaders()->toArray(),
                                'query'   => $request->getQuery()->toArray(),
                                'post'    => $request->getPost()->toArray(),
                            ],
                            'response' => [
                                [
                                    'status'  => $response->getStatusCode(),
                                    'headers' => $response->getHeaders()->toArray(),
                                    'content' => json_decode($response->getContent(), true),
                                ],
                            ],
                        ]
                    ),
                ]
            );
        });
    }
}