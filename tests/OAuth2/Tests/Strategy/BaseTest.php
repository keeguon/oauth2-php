<?php

namespace OAuth2\Tests\Strategy;

class BaseTest extends \OAuth2\Tests\TestCase
{
  /**
   * @covers OAuth2\Strategy\Base::construct()
   */
   public function testConstructorBuildsBase()
   {
     // should initialize with a Client
     $this->setExpectedException('\InvalidArgumentException');
     new \OAuth2\Strategy\Base;
   }
}
