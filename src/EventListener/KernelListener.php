<?php

namespace Fgir\FosRestUtilsBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class KernelListener
{
    private $logger;
    private $logApiCalls;

    public function __construct(LoggerInterface $logger, bool $logApiCalls)
    {
        $this->logger = $logger;
        $this->logApiCalls = $logApiCalls;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        } else {

            if ($this->logApiCalls === true) {
                $headers = $event->getRequest()->server->getHeaders();
                $infos = [
                    'app.event' => 'api.request',
                    'url' => $event->getRequest()->getPathInfo(),
                ];
                $this->logger->notice('Api call', $infos);
            }

        }
    }

}
