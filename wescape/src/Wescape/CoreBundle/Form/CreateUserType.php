<?php

namespace Wescape\CoreBundle\Form;


use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Wescape\CoreBundle\Validator\Constraint\ClientExists;

class CreateUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                "constraints" => new Email()
            ])
            ->add('plainPassword')
            ->add('deviceKey', TextType::class, [
                'required' => false
            ])
            ->add('client', TextType::class, [
                "mapped" => false,
                "constraints" => new ClientExists()
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Wescape\CoreBundle\Entity\User'
        ));
    }

    public function getParent() {
        return RegistrationFormType::class;
    }
}