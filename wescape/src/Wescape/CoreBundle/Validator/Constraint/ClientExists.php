<?php

namespace Wescape\CoreBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

class ClientExists extends Constraint
{
    public $idNotFound = "The array does not contain an 'id' field";
    public $secretNotFound = "The array does not contain a 'secret' field";
    public $invalidClientIdFormat = "The string %id% is no a valid client id format";
    public $invalidClient = "The client is invalid";

    public function validatedBy() {
        return 'exists.client.validator';
    }

    public function getTargets() {
        return Constraint::CLASS_CONSTRAINT;
    }
}