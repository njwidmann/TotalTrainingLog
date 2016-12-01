<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/17/2014
 * Time: 9:09 PM
 */

class test {




}

$obj = (object) array('foo' => 'bar', 'property' => 'value');

$obj2 = json_encode($obj);

echo $obj2;