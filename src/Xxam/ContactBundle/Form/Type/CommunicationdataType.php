<?php

namespace Xxam\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommunicationdataType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact_id')
            ->add('communicationdatatype_id')
            ->add('value')
            ->add('created')
            ->add('updated')
            ->add('contact')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\ContactBundle\Entity\Communicationdata'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'xxam_contactbundle_communicationdata';
    }
}
