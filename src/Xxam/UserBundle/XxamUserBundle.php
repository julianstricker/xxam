<?php

namespace Xxam\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamUserBundle extends Bundle {

    public function getParent() {
        return 'FOSUserBundle';
    }

}
