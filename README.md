# OAuth2-PHP [![Build Status](https://secure.travis-ci.org/Keeguon/oauth2-php.png)](http://travis-ci.org/Keeguon/oauth2-php)


A PHP library aimed to consume services using OAuth 2 as a security scheme.


## Dependencies

* PHP >=5.3.2
* Guzzle


## Installation

### composer

To install OAuth2-PHP with composer you simply need to create a composer.json in your project root and add:

```json
{
    "require": {
        "keeguon/oauth2-php": ">=1.0.0"
    }
}
```

Then run

```bash
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

You have now OAuth2-PHP installed in vendor/keeguon/oauth2-php

And an handy autoload file to include in you project in vendor/.composer/autoload.php


## Testing

The library is fully tested with PHPUnit for unit tests. To run tests you need PHPUnit which can be installed using the project dependencies as follows:

```bash
$ php composer.phar install --dev
```

Then to run the test suites

```bash
$ vendor/bin/phpunit
```


## License

Copyright (c) 2012 Félix Bellanger <felix.bellanger@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
