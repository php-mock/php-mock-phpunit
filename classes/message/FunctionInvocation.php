<?php

namespace phpmock\phpunit\message;

/**
 * A function invocation. This is a proxy for the invocation of the support
 * class. It will print the actual mocked function name instead of the
 * name of the support class.
 *
 * This is part of the needed infrastructure to allow printing helpful
 * error messages about the mocked function.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @internal
 */
final class FunctionInvocation implements \PHPUnit_Framework_MockObject_Invocation
{

    /**
     * @var \PHPUnit_Framework_MockObject_Invocation The invocation subject.
     */
    private $subject;
    
    /**
     * @var string The function name.
     */
    private $name;
    
    /**
     * Sets the function name and the proxied invocation.
     *
     * @param string $name function name
     * @param \PHPUnit_Framework_MockObject_Invocation $invocation proxied invocation
     */
    public function __construct($name, \PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->name    = $name;
        $this->subject = $invocation;
    }
    
    /**
     * Delegates every call to the subject.
     *
     * @param string $method method name
     * @param array $arguments arguments
     * @return mixed result from the proxy
     */
    public function __call($method, array $arguments)
    {
        return call_user_func_array([$this->subject, $method], $arguments);
    }
    
    /**
     * Exposes every public member of the subject.
     *
     * @param string $name member name
     * @return mixed member value
     */
    public function &__get($name)
    {
        return $this->subject->$name;
    }
    
    /**
     * Returns the name of the mocked function.
     *
     * @return string function name
     */
    public function toString()
    {
        return "{$this->name}()";
    }

    public function generateReturnValue()
    {
        return $this->subject->generateReturnValue();
    }
}
