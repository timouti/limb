<?php

set_include_path(dirname(__FILE__) . '/../../../../');
require_once('limb/core/common.inc.php');
require_once('limb/core/src/lmbObject.class.php');

class Foo extends lmbObject
{
  function getBar()
  {
    return 'bar';
  }
}
$object = new Foo(array('foo' => 'foo'));

for($i=0;$i<1000;$i++)
  $object->get('heating_up');

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->get('foo');

echo "get('foo'): " . (microtime(true) - $mark) . "\n";

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->get('foo' . $i);

echo "get('fooXXX'): " . (microtime(true) - $mark) . "\n";

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->get('bar');

echo "get('bar') => getBar(): " . (microtime(true) - $mark) . "\n";

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->getBar();

echo "istance getBar(): " . (microtime(true) - $mark) . "\n";

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->getFoo();

echo "dynamic getFoo(): " . (microtime(true) - $mark) . "\n";

$mark = microtime(true);

for($i=0;$i<1000;$i++)
  $object->foo;

echo "->foo: " . (microtime(true) - $mark) . "\n";