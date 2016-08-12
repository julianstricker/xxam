<?php

namespace Xxam\DynmodBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynmodType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('description')
            ->add('help')
            ->add('iconcls')
            ->add('additionalroles')
            ->add('actions')
            ->add('objectactions')
            ->add('active')

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\DynmodBundle\Entity\Dynmod'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'xxam_dynmodbundle_dynmod';
    }
}
