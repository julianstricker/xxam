<?php

namespace Just\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact_id')
            ->add('contacttype_id')
            ->add('organizationname')
            ->add('surname')
            ->add('firstname')
            ->add('nameprefix')
            ->add('middlename')
            ->add('namesuffix')
            ->add('nickname')
            ->add('vat')
            ->add('tax')
            ->add('birthdate')
            ->add('photo')
            ->add('organizationfunction')
            ->add('created')
            ->add('updated')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Just\AdminBundle\Entity\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'just_adminbundle_contact';
    }
}
