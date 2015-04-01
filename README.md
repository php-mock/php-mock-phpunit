# Mock PHP built-in functions with PHPUnit

This package integrates the function mock library
[PHP-Mock](https://github.com/php-mock/php-mock) with PHPUnit.

# Requirements

# Installation

# Usage

PHP-Mock integrates with the trait
[`PHPMock`](http://php-mock.github.io/phpunit/api/class-phpmock.phpunit.PHPMock.html)
to integrate into your PHPUnit-4 test case. This trait extends the framework
by the method
[`getFunctionMock()`](http://php-mock.github.io/phpunit/api/class-phpmock.phpunit.PHPMock.html#_getFunctionMock).
With this method you can build a mock in the way you are used to build a
PHPUnit mock:

```php
<?php

namespace foo;

use phpmock\phpunit\PHPMock;

class FooTest extends \PHPUnit_Framework_TestCase
{

    use PHPMock;

    public function testBar()
    {
        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->once())->willReturn(3);
        $this->assertEquals(3, time());
    }
}
```

# License and authors

This project is free and under the WTFPL.
Responsable for this project is Markus Malkusch markus@malkusch.de.

## Donations

If you like this project and feel generous donate a few Bitcoins here:
[1335STSwu9hST4vcMRppEPgENMHD2r1REK](bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK)

[![Build Status](https://travis-ci.org/php-mock/phpunit.svg?branch=master)](https://travis-ci.org/php-mock/phpunit)