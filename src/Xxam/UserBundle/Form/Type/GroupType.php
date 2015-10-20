<?php

namespace Xxam\UserBundle\Form\Type;
use Xxam\UserBundle\Entity\Group;
use Xxam\UserBundle\Transformer\RolesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
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
            ->add('name')
            ->add('roles' ,'choice' ,array('choices'=>$roledefinitions,'multiple'=>true,'expanded'=>true )) 
        ;
        
        //$builder->get('roles')->addModelTransformer(new RolesTransformer());
        
    }

    public function getName()
    {
        return 'xxam_userbundle_grouptype';
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
            'data_class' => 'Xxam\UserBundle\Entity\Group',
            //'validation_groups' => array('Brandnamic\CoreBundle\Entity\Group'),
            'lang' => 'de',
        )); 
    }
}