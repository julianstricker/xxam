<?php

namespace Xxam\FilemanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class FilesystemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }
    

    /**
     * @return string
     */
    public function getName()
    {
        return 'xxam_contactbundle_contact';
    }
    
    

}
