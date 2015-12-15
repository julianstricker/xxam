<?php
namespace Xxam\UserBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;


class RolesTransformer implements DataTransformerInterface
{

    public function transform($tags)
    {
        return explode(',', $tags);

    }

    public function reverseTransform($tags)
    {

        return explode(',', $tags);
    }

}