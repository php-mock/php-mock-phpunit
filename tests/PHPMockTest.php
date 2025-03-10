<?php

namespace phpmock\phpunit;

use phpmock\AbstractMockTestCase;
use phpmock\Deactivatable;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Tests PHPMock.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @see PHPMock
 */
class PHPMockTest extends AbstractMockTestCase
{

    use PHPMock;

    protected function defineFunction($namespace, $functionName)
    {
        self::defineFunctionMock($namespace, $functionName);
    }

    protected function mockFunction($namespace, $functionName, callable $function)
    {
        $mock = $this->getFunctionMock($namespace, $functionName);
        $mock->expects($this->any())->willReturnCallback($function);
    }

    protected function disableMocks()
    {
    }

    /**
     * Tests building a mock with arguments.
     *
     * @test
     */
    public function testFunctionMockWithArguments()
    {
        $time = $this->getFunctionMock(__NAMESPACE__, "sqrt");
        $time->expects($this->once())->with(9)->willReturn(2);

        $this->assertEquals(2, sqrt(9));
    }

    /**
     * Tests failing an expectation.
     *
     * @test
     */
    public function testFunctionMockFailsExpectation()
    {
        try {
            $time = $this->getFunctionMock(__NAMESPACE__, "time");
            $time->expects($this->once());

            $time->__phpunit_verify();
            $this->fail("Expectation should fail");
        } catch (ExpectationFailedException $e) {
            time(); // satisfy the expectation
        }
    }

    /**
     * Register a Deactivatable for a tear down.
     *
     * @test
     */
    public function testRegisterForTearDownRegistered()
    {
        $obj = new \stdClass();
        $obj->count = 0;

        $class = new class ($obj) implements Deactivatable
        {
            private $obj;

            public function __construct($obj)
            {
                $this->obj = $obj;
            }

            public function disable()
            {
                ++$this->obj->count;
            }
        };
        $this->registerForTearDown($class);

        self::assertSame(0, $obj->count);

        return $obj;
    }

    /**
     * Check the Deactivatable was executed on a tear down of dependent test.
     *
     * @test
     *
     * @depends testRegisterForTearDownRegistered
     */
    #[\PHPUnit\Framework\Attributes\Depends('testRegisterForTearDownRegistered')]
    public function testRegisterForTearDownExecuted($obj)
    {
        self::assertSame(1, $obj->count);

        return $obj;
    }

    /**
     * Check the Deactivatable was unregistered after executing, so it is not executed again.
     *
     * @test
     *
     * @depends testRegisterForTearDownExecuted
     */
    #[\PHPUnit\Framework\Attributes\Depends('testRegisterForTearDownExecuted')]
    public function testRegisterForTearDownRemoved($obj)
    {
        self::assertSame(1, $obj->count);
    }
}
