<?php

namespace Xxam\FilemanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilesystemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filesystemadapters=(array_keys($options['filesystemadapters']));
        $builder
            //->add('user_id')
            ->add('user')
            ->add('filesystemname')
            ->add('adapter', ChoiceType::class, array('required' => true,'choices'=> $filesystemadapters, 'placeholder' => '', 'empty_data'  => null))
            ->add('settings',HiddenType::class)
            ->add('groups')


        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\FilemanagerBundle\Entity\Filesystem',
            'filesystemadapters'=>[]
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
