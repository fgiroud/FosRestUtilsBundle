<?php
namespace Fgir\FosRestUtilsBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

abstract class ApiController extends FOSRestController
{
    public function getEntityManager()
    {
        return $this->get('entity_manager');
    }

    protected function throw404Unless($object)
    {
        if (!$object) {
            throw $this->createNotFoundException("Unable to find object");
        }
    }

    protected function throw404UnlessDocument($document, $documentType, $referer)
    {
        if (!$document) {
            throw $this->createNotFoundException("Unable to find " . $documentType . ' with id = ' . $referer);
        }
    }
}
