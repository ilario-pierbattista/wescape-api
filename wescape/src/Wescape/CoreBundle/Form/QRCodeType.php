<?php

namespace Wescape\CoreBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class QRCodeType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('node', EntityType::class, array(
                'class'        => 'CoreBundle:Node',
                'choice_label' => 'name',
                'label' => 'Nodo',
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Genera QR',
                'attr' => [
                    'class' => 'btn btn-gl btn-wescape-red'
                ]
            ));
    }
}