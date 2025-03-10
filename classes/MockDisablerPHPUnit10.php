<?php

namespace phpmock\phpunit;

use phpmock\Deactivatable;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

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
class MockDisablerPHPUnit10 implements FinishedSubscriber
{
    /**
     * @var Deactivatable The function mocks.
     */
    private $deactivatable;

    /**
     * @var callable|null The callback to execute after the test.
     */
    private $callback;
    
    /**
     * Sets the function mocks.
     *
     * @param Deactivatable $deactivatable The function mocks.
     * @param callback|null $callback      The callback to execute after the test.
     */
    public function __construct(Deactivatable $deactivatable, ?callable $callback = null)
    {
        $this->deactivatable = $deactivatable;
        $this->callback = $callback;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function notify(Finished $event) : void
    {
        $this->deactivatable->disable();
        if ($this->callback !== null) {
            call_user_func($this->callback, $this);
        }
    }

    public function endTest(): void
    {
        $this->deactivatable->disable();
        if ($this->callback !== null) {
            call_user_func($this->callback, $this);
        }
    }
}
