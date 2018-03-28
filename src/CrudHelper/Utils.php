<?php

namespace Fgir\FosRestUtilsBundle\CrudHelper;

use Symfony\Component\Form\FormInterface;

class Utils
{
    public static function getFormErrors(FormInterface $form)
    {
        return self::convertFormToArray($form);
    }

    /**
     * This code has been taken from JMSSerializer.
     */
    private static function convertFormToArray(FormInterface $data)
    {
        $result = [];

        foreach ($data->getErrors() as $error) {
            $result[] = $error->getMessage();
        }

        foreach ($data->all() as $child) {
            $errs = $child->getErrors();
            if (count($errs)) {
                $result[$child->getName()] = $errs[0]->getMessage();
            }
        }

        return $result;
    }
}
