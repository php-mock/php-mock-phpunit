<?php

namespace phpmock\phpunit;

use phpmock\integration\MockDelegateFunctionBuilder;
use phpmock\MockBuilder;
use phpmock\Deactivatable;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Adds building a function mock functionality into \PHPUnit\Framework\TestCase.
 *
 * Use this trait in your \PHPUnit\Framework\TestCase:
 * <code>
 * <?php
 *
 * namespace foo;
 *
 * class FooTest extends \PHPUnit\Framework\TestCase
 * {
 *
 *     use \phpmock\phpunit\PHPMock;
 *
 *     public function testBar()
 *     {
 *         $time = $this->getFunctionMock(__NAMESPACE__, "time");
 *         $time->expects($this->once())->willReturn(3);
 *         $this->assertEquals(3, time());
 *     }
 * }
 * </code>
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
trait PHPMock
{

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * This method exists in \PHPUnit\Framework\TestCase.
     *
     * @param string $className Name of the class to mock.
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     * @see \PHPUnit\Framework\TestCase::getMockBuilder()
     * @internal
     */
    abstract protected function getMockBuilder($className);

    /**
     * Returns the test result.
     *
     * This method exists in \PHPUnit\Framework\TestCase.
     *
     * @return \PHPUnit\Framework\TestResult The test result.
     * @see \PHPUnit\Framework\TestCase::getTestResultObject()
     * @internal
     */
    abstract protected function getTestResultObject();

    /**
     * Returns the enabled function mock.
     *
     * This mock will be disabled automatically after the test run.
     *
     * @param string $namespace The function namespace.
     * @param string $name      The function name.
     *
     * @return MockObject The PHPUnit mock.
     */
    public function getFunctionMock($namespace, $name)
    {
        $delegateBuilder = new MockDelegateFunctionBuilder();
        $delegateBuilder->build($name);
        
        $mock = $this->getMockBuilder($delegateBuilder->getFullyQualifiedClassName())->getMockForAbstractClass();
        
        $mock->__phpunit_getInvocationMocker()->addMatcher(new DefaultArgumentRemover());
        
        $functionMockBuilder = new MockBuilder();
        $functionMockBuilder->setNamespace($namespace)
                            ->setName($name)
                            ->setFunctionProvider($mock);
                
        $functionMock = $functionMockBuilder->build();
        $functionMock->enable();
        
        $this->registerForTearDown($functionMock);
        
        $proxy = new MockObjectProxy($mock);
        return $proxy;
    }
    
    /**
     * Automatically disable function mocks after the test was run.
     *
     * If you use getFunctionMock() you don't need this method. This method
     * is meant for a Deactivatable (e.g. a MockEnvironment) which was
     * directly created with PHPMock's API.
     *
     * @param Deactivatable $deactivatable The function mocks.
     */
    public function registerForTearDown(Deactivatable $deactivatable)
    {
        $result = $this->getTestResultObject();
        $result->addListener(new MockDisabler($deactivatable));
    }
    
    /**
     * Defines the mocked function in the given namespace.
     *
     * In most cases you don't have to call this method. {@link getFunctionMock()}
     * is doing this for you. But if the mock is defined after the first call in the
     * tested class, the tested class doesn't resolve to the mock. This is
     * documented in Bug #68541. You therefore have to define the namespaced
     * function before the first call (e.g. with the beforeClass annotation).
     *
     * Defining the function has no side effects. If the function was
     * already defined this method does nothing.
     *
     * @see getFunctionMock()
     * @link https://bugs.php.net/bug.php?id=68541 Bug #68541
     *
     * @param string $namespace The function namespace.
     * @param string $name      The function name.
     */
    public static function defineFunctionMock($namespace, $name)
    {
        $functionMockBuilder = new MockBuilder();
        $functionMockBuilder->setNamespace($namespace)
                            ->setName($name)
                            ->setFunction(function () {
                            })
                            ->build()
                            ->define();
    }
}
