<?php

namespace Fgir\FosRestUtilsBundle\Controller;

use FOS\RestBundle\Controller\ExceptionController as FosExceptionController;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends FosExceptionController
{
    protected function getParameters(ViewHandler $viewHandler, $currentContent, $code, $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters = parent::getParameters($viewHandler, $currentContent, $code, $exception);

        if ($this->container->get('kernel')->isDebug() || $this->container->get('kernel')->getEnvironment() == 'test_func') {
            $parameters['errors'] = $parameters['exception'];
        }

        return $parameters;
    }

}
