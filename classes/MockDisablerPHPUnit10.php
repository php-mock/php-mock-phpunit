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
     * Sets the function mocks.
     *
     * @param Deactivatable $deactivatable The function mocks.
     */
    public function __construct(Deactivatable $deactivatable)
    {
        $this->deactivatable = $deactivatable;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function notify(Finished $event) : void
    {
        $this->deactivatable->disable();
    }

    public function endTest(): void
    {
        $this->deactivatable->disable();
    }
}
