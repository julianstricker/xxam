<?php

namespace Just\UserBundle\Form;
use Just\UserBundle\Entity\User; 
use Just\UserBundle\Transformer\RolesTransformer; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder ,$options);
        
        $rds=$this->roledefinitions;
        $roledefinitions=Array();
        if ($rds){
            foreach($rds as $key => $value){
               $roledefinitions[$value]=$value; 
            }
        }
        //dump($roledefinitions);
        $builder
            ->add('username')
            ->add('email')
            ->add('passwordplain','password',array('mapped'=>false))
            ->add('locked')
            ->add('groups') 
            ->add('roles' ,'choice' ,array('choices'=>$roledefinitions,'multiple'=>true,'expanded'=>true )) 
        ;
        
        //$builder->get('roles')->addModelTransformer(new RolesTransformer());
        
    }

    public function getName()
    {
        return 'just_userbundle_grouptype';
    }
    
    protected $roledefinitions;
    public function __construct ($roledefinitions)
    {
        $this->roledefinitions = $roledefinitions;        
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('lang')); 
        $resolver->setDefaults(array(
            'data_class' => 'Just\UserBundle\Entity\User',
            //'validation_groups' => array('Brandnamic\AdminBundle\Entity\User'),
            'lang' => 'de',
        )); 
    }
}
