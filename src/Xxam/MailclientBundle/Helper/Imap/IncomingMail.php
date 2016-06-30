<?php

/**
 * @see https://github.com/barbushin/php-imap
 * @author Julian Stricker based on Barbushin Sergey http://linkedin.com/in/barbushin
 *
 */

namespace Xxam\MailclientBundle\Helper\Imap;

use PhpImap\IncomingMail as BaseIncomingMail;


class IncomingMail extends BaseIncomingMail{
    public $headers;
    public $hasexternallinks;
    public $files;

}