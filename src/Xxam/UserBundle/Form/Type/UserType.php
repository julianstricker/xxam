<?php

namespace Xxam\UserBundle\Form\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder ,$options);

        $rds=$this->roledefinitions=$options['roledefinitions'];
        $roledefinitions=Array();
        if ($rds){
            foreach($rds as $key => $value){
               $roledefinitions[$value]=$value; 
            }
        }
        $builder
            ->add('username')
            ->add('email')
            ->add('passwordplain',PasswordType::class,array('mapped'=>false))
            ->add('locked')
            ->add('groups') 
            ->add('roles' ,ChoiceType::class ,array('choices'=>$roledefinitions,'multiple'=>true,'expanded'=>true ))
        ;
        
        //$builder->get('roles')->addModelTransformer(new RolesTransformer());
        
    }

    public function getName()
    {
        return 'xxam_userbundle_grouptype';
    }
    
    protected $roledefinitions;
    public function __construct ()
    {

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('lang'));
        $resolver->setDefaults(array(
            'data_class' => 'Xxam\UserBundle\Entity\User',
            //'validation_groups' => array('Brandnamic\CoreBundle\Entity\User'),
            'lang' => 'de',
            'roledefinitions' => []
        )); 
    }
}
