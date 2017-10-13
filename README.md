# Mock PHP built-in functions with PHPUnit

This package integrates the function mock library
[PHP-Mock](https://github.com/php-mock/php-mock) with PHPUnit.

# Installation

Use [Composer](https://getcomposer.org/):

```sh
composer require --dev php-mock/php-mock-phpunit
```

# Usage

PHP-Mock integrates with the trait
[`PHPMock`](http://php-mock.github.io/php-mock-phpunit/api/class-phpmock.phpunit.PHPMock.html)
into your PHPUnit test case. This trait extends the framework
by the method
[`getFunctionMock()`](http://php-mock.github.io/php-mock-phpunit/api/class-phpmock.phpunit.PHPMock.html#_getFunctionMock).
With this method you can build a mock in the way you are used to build a
PHPUnit mock:

## Examples
1. Test built-in function with mocked results within same namespace.
```php
<?php

namespace Foo;

class BuiltinTest extends \PHPUnit\Framework\TestCase
{

    use \phpmock\phpunit\PHPMock;

    public function testTime()
    {
        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->once())->willReturn(3);

        $this->assertEquals(3, time());
    }

    public function testExec()
    {
        $exec = $this->getFunctionMock(__NAMESPACE__, "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output, &$return_var) {
                $this->assertEquals("foo", $command);
                $output = ["failure"];
                $return_var = 1;
            }
        );

        exec("foo", $output, $return_var);
        $this->assertEquals(["failure"], $output);
        $this->assertEquals(1, $return_var);
    }
}
```

2. When class to be tested and the test class are in different namespace.

```php
<?php
// Source class to be tested
namespace Foo;
 
class TimeClass
{
    public function oneHourAgo()
    {
        return date('H:i:s', time() - 3600);
    }
}
```
```php
<?php
// Tests for TimeClass
namespace Tests\Foo;
 
use PHPUnit\Framework\TestCase;
 
class TimeClassTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     * @var TimeClass
     */
    private $timeClass;
 
    /**
     * Create test subject before test
     */
    protected function setUp()
    {
        parent::setUp();
        $this->timeClass = new TimeClass();
    }
    
    protected function tearDown()
    {
        this->timeClass = null;
        parent::tearDown();
    }
 
    /*
     * Test cases
     */
    public function testOneHourAgoFromNoon()
    {
        $time = $this->getFunctionMock('\\Foo', 'time');
        $time->expects($this->once())->willReturn(strtotime('12:00'));

        $this->assertEquals('11:00', $this->timeClass->oneHourAgo());
    }
    
    public function testOneHourAgoFromMidnight()
    {
        $time = $this->getFunctionMock('\\Foo', 'time');
        $time->expects($this->once())->willReturn(strtotime('0:00'));

        $this->assertEquals('23:00', $this->timeClass->oneHourAgo());
    }
}
```


There's no need to disable the mocked function. The PHPUnit integration does
that for you.

## Restrictions

This library comes with the same restrictions as the underlying
[`php-mock`](https://github.com/php-mock/php-mock#requirements-and-restrictions):

* Only *unqualified* function calls in a namespace context can be mocked.
  E.g. a call for `time()` in the namespace `foo` is mockable,
  a call for `\time()` is not.

* The mock has to be defined before the first call to the unqualified function
  in the tested class. This is documented in [Bug #68541](https://bugs.php.net/bug.php?id=68541).
  In most cases you can ignore this restriction. But if you happen to run into
  this issue you can call [`PHPMock::defineFunctionMock()`](http://php-mock.github.io/php-mock-phpunit/api/class-phpmock.phpunit.PHPMock.html#_defineFunctionMock)
  before that first call (e.g. with `@beforeClass`).
  This would define a side effectless namespaced function. Another effective
  approach is running your test in an isolated process (e.g. with `@runInSeparateProcess`).

# License and authors

This project is free and under the WTFPL.
Responsable for this project is Markus Malkusch markus@malkusch.de.

## Donations

If you like this project and feel generous donate a few Bitcoins here:
[1335STSwu9hST4vcMRppEPgENMHD2r1REK](bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK)

[![Build Status](https://travis-ci.org/php-mock/php-mock-phpunit.svg?branch=master)](https://travis-ci.org/php-mock/php-mock-phpunit)

