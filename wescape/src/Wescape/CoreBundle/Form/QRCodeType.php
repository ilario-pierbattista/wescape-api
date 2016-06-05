<?php

namespace Wescape\CoreBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'label' => 'Nodo da codificare',
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('extension', ChoiceType::class, array(
                'choices' => [
                    'jpg' => 'jpg',
                    'png' => 'png',
                    'gif' => 'gif',
                ],
                'mapped' => FALSE,
                'label' => 'Formato immagine',
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('size', ChoiceType::class, array(
                'choices' => [
                    '100px' => 100,
                    '200px' => 200,
                    '500px' => 500,
                    '1000px' => 1000
                ],
                'mapped' => FALSE,
                'label' => 'Dimensione immagine',
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('padding', ChoiceType::class, array(
                'choices' => [
                    '10px' => 10,
                    '20px' => 20,
                    '50px' => 50,
                ],
                'mapped' => FALSE,
                'label' => 'Margini interni immagine',
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

    /**
     * @param OptionsResolver $resolver
     */
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => 'Wescape\CoreBundle\Entity\Node'
//        ));
//    }
}