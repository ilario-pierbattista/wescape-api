<?php

namespace Wescape\CoreBundle\Validator\Constraint;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Wescape\CoreBundle\Entity\Client;

class ClientExistsValidator extends ConstraintValidator
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    public function validate($value, Constraint $constraint) {
        /** @var ClientExists $constraint */
        if(!is_array($value)) {
            $this->context->buildViolation($constraint->clientDataNotFound)
                ->addViolation();
        } elseif (!array_key_exists("id", $value)) {
            $this->context->buildViolation($constraint->idNotFound)
                ->addViolation();
        } elseif (!array_key_exists("secret", $value)) {
            $this->context->buildViolation($constraint->secretNotFound)
                ->addViolation();
        } else {
            $client_id = explode("_", $value["id"]);
            $client_secret = $value["secret"];

            if (count($client_id) != 2 || $client_id[0] == "") {
                $this->context->buildViolation($constraint->invalidClientIdFormat)
                    ->setParameter("%id%", $value['id'])
                    ->addViolation();
            } else {
                /** @var Client $foundClient */
                $foundClient = $this->em->getRepository("CoreBundle:Client")
                    ->find($client_id[0]);
                if ($foundClient === null ||
                    $foundClient->getRandomId() != $client_id[1] ||
                    $foundClient->getSecret() != $client_secret
                ) {
                    $this->context->buildViolation($constraint->invalidClient)
                        ->addViolation();
                }
            }
        }
    }
}