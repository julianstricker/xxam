<?php

namespace Xxam\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contacttypes=array_flip($options['contacttypes']);
        $builder
            ->add('contact_id')
            ->add('contacttype_id', ChoiceType::class, array('required' => true,'choices'=> $contacttypes, 'placeholder' => '', 'empty_data'  => null))
            ->add('organizationname')
            ->add('lastname')
            ->add('firstname')
            ->add('nameprefix')
            ->add('initials')
            ->add('nickname')
            ->add('vat')
            ->add('tax')
            ->add('birthday',DateType::class, array('input'=>'datetime', /*'format' => 'dd.MM.yyyy',*/ 'widget'=>'single_text'))
            ->add('photo')
            ->add('organizationfunction')
            //->add('addresses')
            
                
        ;
    }
    


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\ContactBundle\Entity\Contact',
            'contacttypes' => []
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'xxam_contactbundle_contact';
    }

}
