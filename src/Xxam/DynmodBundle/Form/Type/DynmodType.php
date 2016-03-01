<?php

namespace Xxam\DynmodBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('actions')
            ->add('objectactions')
            ->add('datacontainer_id')


            
                
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
