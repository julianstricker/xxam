<?php
namespace Xxam\CmsAdminBundle\Document;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Page implements RouteReferrersReadInterface
{
    use ContentTrait;

    /**
     * @PHPCR\Nodename()
     */
    protected $box1title;

    /**
     * @PHPCR\String(nullable=true)
     */
    protected $box1content;

    /**
     * @PHPCR\Nodename()
     */
    protected $box2title;

    /**
     * @PHPCR\String(nullable=true)
     */
    protected $box2content;

    /**
     * @PHPCR\Nodename()
     */
    protected $box3title;

    /**
     * @PHPCR\String(nullable=true)
     */
    protected $box3content;

    public function getBox1title()
    {
        return $this->box1title;
    }

    public function setBox1title($box1title)
    {
        $this->box1title = $box1title;
    }

    public function getBox1content()
    {
        return $this->box1content;
    }

    public function setBox1content($box1content)
    {
        $this->box1content = $box1content;
    }


    public function getBox2title()
    {
        return $this->box2title;
    }

    public function setBox2title($box2title)
    {
        $this->box2title = $box2title;
    }

    public function getBox2content()
    {
        return $this->box2content;
    }

    public function setBox2content($box2content)
    {
        $this->box2content = $box2content;
    }



    public function getBox3title()
    {
        return $this->box3title;
    }

    public function setBox3title($box3title)
    {
        $this->box3title = $box3title;
    }

    public function getBox3content()
    {
        return $this->box3content;
    }

    public function setBox3content($box3content)
    {
        $this->box3content = $box3content;
    }
}