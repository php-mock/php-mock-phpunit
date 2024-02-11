<?php

namespace phpmock\phpunit;

use phpmock\Mock;
use PHPUnit\Framework\TestCase;

/**
 * Tests MockDisabler.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @see MockDisabler
 */
class MockDisablerTest extends TestCase
{

    /**
     * Tests disabling the mock.
     */
    public function testEndTest()
    {
        $min = new Mock(__NAMESPACE__, "min", "max");
        $min->enable();
        $this->assertEquals(9, min(1, 9));
        
        $disabler = new MockDisabler($min);
        $disabler->endTest($this, 1);
        
        $this->assertEquals(1, min(1, 9));
    }
}
