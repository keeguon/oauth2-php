<?php

// autoloader
require __DIR__.'/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
  'OAuth2'  => __DIR__.'/../src',
));

$loader->registerPrefixes(array(
  'EpiCurl_' => __DIR__.'/../src/vendor/php-multi-curl',
));

$loader->register();

