<?php

if (false === class_exists('Symfony\Component\ClassLoader\UniversalClassLoader', false)) {
  require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
}

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'  => __DIR__.'/vendor'
  , 'Epiphany' => __DIR__.'/vendor'
  , 'OAuth2'   => __DIR__.'/src',
));
$loader->registerPrefixes(array(
  'EpiCurl_' => __DIR__.'/vendor',
));
$loader->register();
