<?php

namespace Xxam\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
