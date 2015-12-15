<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xxam\CoreBundle\Twig;

class XxamExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('jscode', array($this, 'jscodeFilter'), array('is_safe' => array('all'))),
        );
    }

    public function jscodeFilter($data,$options = 0)
    {
        $return = json_encode($data,$options);
        return $return;
    }

    public function getName()
    {
        return 'xxam_extension';
    }
}