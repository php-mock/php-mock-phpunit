<?php

namespace phpmock\phpunit\message;

/**
 * An InvocationMocker which converts invocations into function invocations.
 *
 * This is part of the needed infrastructure to allow printing helpful
 * error messages about the mocked function.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @internal
 */
final class FunctionInvocationMocker extends \PHPUnit_Framework_MockObject_InvocationMocker
{
    
    /**
     * @var string The Function name.
     */
    private $name;
    
    /**
     * Sets the function name.
     *
     * @param type $name function name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function invoke(\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return parent::invoke(new FunctionInvocation($this->name, $invocation));
    }
}
