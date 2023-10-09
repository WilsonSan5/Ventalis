<?php

namespace App\Tests\unit;

use App\MyClass;

use PHPUnit\Framework\TestCase;

class MyClassTest extends TestCase
{
    public function testMyMethod()
    {
        // $myClass = new MyCLass();
        // $result = $myClass->addition(2,3);
        $this->assertEquals(5, 2 + 3);
    }
}