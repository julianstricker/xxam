<?php
namespace Xxam\UserBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Fuz\AppBundle\Entity\FiddleTag;

class RolesTransformer implements DataTransformerInterface
{

    public function transform($tags)
    {
        return explode(',', $tags);
        dump($tags);
        return $tags;
        if(is_array($tags)){
            return $tags;
        }elseif($tags==''){
            return Array();
        }else{
            return implode(',', $tags);
        }
    }

    public function reverseTransform($tags)
    {
        echo 'xxx';
        dump($tags);
        return explode(',', $tags);
    }

}