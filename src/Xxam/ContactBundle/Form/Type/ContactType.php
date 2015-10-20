<?php

namespace Xxam\ContactBundle\Form\Type;

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
            ->add('contacttype_id', 'choice', array('required' => true,'choices'=> $this->contacttypes, 'empty_value' => '', 'empty_data'  => null))
            ->add('organizationname')
            ->add('lastname')
            ->add('firstname')
            ->add('nameprefix')
            ->add('initials')
            ->add('nickname')
            ->add('vat')
            ->add('tax')
            ->add('birthday','date',array('input'=>'datetime', /*'format' => 'dd.MM.yyyy',*/ 'widget'=>'single_text'))
            ->add('photo')
            ->add('organizationfunction')
            //->add('addresses')
            
                
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\ContactBundle\Entity\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'xxam_contactbundle_contact';
    }
    
    
    protected $em;
    protected $contacttypes;
    
    public function __construct($contacttypes)
    {
        //$this->em = $em;
        $this->contacttypes = $contacttypes;
    }
}
