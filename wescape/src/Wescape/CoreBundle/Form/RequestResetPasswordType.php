<?php

namespace Wescape\CoreBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Wescape\CoreBundle\Validator\Constraint\ClientExists;

class RequestResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => new Email()
            ])
            ->add('client', ClientType::class, [
                "mapped" => false,
                "constraints" => new ClientExists()
            ]);
    }
}