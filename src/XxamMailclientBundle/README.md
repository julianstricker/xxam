Xxam/MailclientBundle - A complete mailclient bundle for Xxam
=============================================================


Overview
========

License
=======

This bundle is released under the [MIT license](Resources/meta/LICENSE)

Installation
============

## Step1: Using Composer

Add the following line to your composer.json require block:

```js
// composer.json
{
    // ...
    require: {
        // ...
        "xxam/mailclientbundle": "dev-master"
    }
}
```

Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

```bash
$ php composer.phar update
```

### Step 2: Register the Bundle

Modify your AppKernel with the following line:
```php
<?php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new XxamMailclientBundle\XxamMailclientBundle(),
    // ...
);
```
