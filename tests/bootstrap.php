<?php

if (false === class_exists('Symfony\Component\ClassLoader\UniversalClassLoader', false)) {
  require_once __DIR__.'/../vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
}

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'       => __DIR__.'/../vendor'
  , 'Guzzle'        => __DIR__.'/../vendor/guzzle/src'
  , 'OAuth2'        => array(__DIR__.'/../src', __DIR__)
));
$loader->register();
