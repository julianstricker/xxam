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
            new \Twig_SimpleFilter('jsonwithfunctions', array($this, 'jsonwithfunctionsFilter'), array('is_safe' => array('all'))),
            new \Twig_SimpleFilter('mailaddrsasextdata', array($this, 'mailaddrsasextdataFilter'), array('is_safe' => array('all'))),
        );
    }

    public function jscodeFilter($data,$options = 0)
    {
        $return = json_encode($data,$options);
        return $return;
    }

    public function jsonwithfunctionsFilter($data){
        array_walk_recursive($data,function(&$item,$key){
            if (is_string($item) && substr($item,0,8)=='function')  $item='$%&functionstart'.$item.'functionend&%$';
        });
        $return = json_encode($data);
        $return=str_replace(['"$%&functionstart','functionend&%$"'],['',''],$return);
        return $return;
    }



    /**
     * @param \stdClass $mail
     * @param string $fieldname
     * @return string
     */
    public function mailaddrsasextdataFilter($mail, $fieldname){
        $return=[];
        if(property_exists($mail,$fieldname) && is_array($mail->{$fieldname})) {
            foreach ($mail->{$fieldname} as $addr => $name) {
                if (!empty($name)) {
                    $return[] = $name . ' <' . $addr . '>';
                } else {
                    $return[] = $addr;
                }
            }
        }
        return json_encode($return);
        //[{% if mail is defined and mail.replyTo is defined %}{% for addr,name in mail.replyTo %}{% if not loop.first %},{% endif %}'{% if name !='' %}{{name}} <{{addr}}>{% else %}{{addr}}{% endif %}'{% endfor %}{% endif %}]
    }

    public function getName()
    {
        return 'xxam_extension';
    }
}