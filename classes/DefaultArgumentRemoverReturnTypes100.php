<?php

namespace phpmock\phpunit;

use phpmock\generator\MockFunctionGenerator;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * Removes default arguments from the invocation.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @internal
 */
class DefaultArgumentRemoverReturnTypes100 extends InvocationOrder
{
    /**
     * @SuppressWarnings(PHPMD)
     */
    public function invokedDo(Invocation $invocation): void
    {
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function matches(Invocation $invocation) : bool
    {
        $iClass = class_exists(Invocation::class);

        if ($iClass
            || $invocation instanceof Invocation\StaticInvocation
        ) {
            $this->removeDefaultArguments(
                $invocation,
                $iClass ? Invocation::class : Invocation\StaticInvocation::class
            );
        } else {
            if (method_exists($invocation, 'parameters')) {
                $parameters = $invocation->parameters();
            } else {
                $parameters =& $invocation->parameters;
            }

            MockFunctionGenerator::removeDefaultArguments($parameters);
        }

        return false;
    }

    public function verify() : void
    {
    }

    /**
     * This method is not defined in the interface, but used in
     * PHPUnit_Framework_MockObject_InvocationMocker::hasMatchers().
     *
     * @return boolean
     * @see \PHPUnit_Framework_MockObject_InvocationMocker::hasMatchers()
     */
    public function hasMatchers()
    {
        return false;
    }

    public function toString() : string
    {
        return __CLASS__;
    }

    /**
     * Remove default arguments from StaticInvocation or its children (hack)
     *
     * @SuppressWarnings(PHPMD)
     */
    private function removeDefaultArguments(Invocation $invocation, string $class)
    {
        $remover = function () {
            if (method_exists($this, 'parameters')) {
                $parameters = $this->parameters();
            } else {
                $parameters =& $this->parameters;
            }

            MockFunctionGenerator::removeDefaultArguments($parameters);
        };

        $remover->bindTo($invocation, $class)();
    }
}
