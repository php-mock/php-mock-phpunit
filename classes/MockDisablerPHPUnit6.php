<?php

namespace phpmock\phpunit;

use Closure;
use phpmock\Deactivatable;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;

/**
 * Test listener for PHPUnit integration.
 *
 * This class disables mock functions after a test was run.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @internal
 */
class MockDisablerPHPUnit6 extends BaseTestListener
{
    /**
     * @var Deactivatable The function mocks.
     */
    private $deactivatable;

    /**
     * @var Closure|null The callback to execute after the test.
     */
    private $callback;
    
    /**
     * Sets the function mocks.
     *
     * @param Deactivatable $deactivatable The function mocks.
     * @param Closure|null $callback       The callback to execute after the test.
     */
    public function __construct(Deactivatable $deactivatable, Closure $callback = null)
    {
        $this->deactivatable = $deactivatable;
        $this->callback = $callback;
    }
    
    /**
     * Disables the function mocks.
     *
     * @param Test $test The test.
     * @param int  $time The test duration.
     *
     * @see Mock::disable()
     */
    public function endTest(Test $test, $time)
    {
        parent::endTest($test, $time);
        
        $this->deactivatable->disable();
        if ($this->callback !== null) {
            ($this->callback)($this);
        }
    }
}
