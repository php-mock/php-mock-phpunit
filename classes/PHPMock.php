<?php

namespace phpmock\phpunit;

use DirectoryIterator;
use phpmock\integration\MockDelegateFunctionBuilder;
use phpmock\MockBuilder;
use phpmock\Deactivatable;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionProperty;
use SebastianBergmann\Template\Template;

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
    public static $templatesPath = '/tmp';

    private $phpunitVersionClass = '\\PHPUnit\\Runner\\Version';
    private $openInvocation = 'new \\PHPUnit\\Framework\\MockObject\\Invocation(';
    private $openWrapper = '\\phpmock\\phpunit\\DefaultArgumentRemover::removeDefaultArgumentsWithReflection(';
    private $closeFunc = ')';

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
        $this->prepareCustomTemplates();

        $delegateBuilder = new MockDelegateFunctionBuilder();
        $delegateBuilder->build($name);

        $builder = $this->getMockBuilder($delegateBuilder->getFullyQualifiedClassName());
        if (is_callable([$builder, 'addMethods'])) {
            $builder->addMethods([$name]);
        }
        $mock = $builder->getMockForAbstractClass();
        $this->addMatcher($mock, $name);

        $functionMockBuilder = new MockBuilder();
        $functionMockBuilder->setNamespace($namespace)
                            ->setName($name)
                            ->setFunctionProvider($mock);

        $functionMock = $functionMockBuilder->build();
        $functionMock->enable();

        $this->registerForTearDown($functionMock);

        return new MockObjectProxy($mock);
    }

    private function addMatcher($mock, $name)
    {
        if (is_callable([$mock, '__phpunit_getInvocationHandler'])) {
            $mocker = $mock->__phpunit_getInvocationHandler()->expects(new DefaultArgumentRemover());
            $mocker->method($name);
            return;
        }
        $mock->__phpunit_getInvocationMocker()->addMatcher(new DefaultArgumentRemover());
    }

    /**
     * Automatically disable function mocks after the test was run.
     *
     * If you use getFunctionMock() you don't need this method. This method
     * is meant for a Deactivatable (e.g. a MockEnvironment) which was
     * directly created with PHPMock's API.
     *
     * @param Deactivatable $deactivatable The function mocks.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function registerForTearDown(Deactivatable $deactivatable)
    {
        if (class_exists(Facade::class)) {
            $facade = Facade::instance();

            $property = new ReflectionProperty(Facade::class, 'sealed');
            $property->setAccessible(true);
            $property->setValue($facade, false);

            $facade->registerSubscriber(
                new MockDisabler($deactivatable)
            );

            $property->setValue($facade, true);

            return;
        }

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

    /**
     * Adds a wrapper method to the Invocable object instance that makes it
     * possible to remove optional parameters when it is declared read-only.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    private function prepareCustomTemplates()
    {
        if (!($this->shouldPrepareCustomTemplates() &&
            is_dir(static::$templatesPath) &&
            ($phpunitTemplatesDir = $this->getPhpunitTemplatesDir())
        )) {
            return;
        }

        $templatesDir = realpath(static::$templatesPath);
        $directoryIterator = new DirectoryIterator($phpunitTemplatesDir);

        $templates = [];

        $prefix = 'phpmock-phpunit-' . $this->getPhpUnitVersion() . '-';

        foreach ($directoryIterator as $fileinfo) {
            if ($fileinfo->getExtension() !== 'tpl') {
                continue;
            }

            $filename = $fileinfo->getFilename();
            $customTemplateFile = $templatesDir . DIRECTORY_SEPARATOR . $prefix . $filename;
            $templateFile = $phpunitTemplatesDir . DIRECTORY_SEPARATOR . $filename;

            $this->createCustomTemplateFile($templateFile, $customTemplateFile);

            if (file_exists($customTemplateFile)) {
                $templates[$templateFile] = new Template($customTemplateFile);
            }
        }

        $mockMethodClasses = [
            'PHPUnit\\Framework\\MockObject\\Generator\\MockMethod',
            'PHPUnit\\Framework\\MockObject\\MockMethod',
        ];

        foreach ($mockMethodClasses as $mockMethodClass) {
            if (class_exists($mockMethodClass)) {
                $reflection = new ReflectionClass($mockMethodClass);

                $reflectionTemplates = $reflection->getProperty('templates');
                $reflectionTemplates->setAccessible(true);

                $reflectionTemplates->setValue(null, $templates);

                break;
            }
        }
    }

    private function shouldPrepareCustomTemplates()
    {
        return class_exists($this->phpunitVersionClass)
            && version_compare($this->getPhpUnitVersion(), '10.0.0') >= 0;
    }

    private function getPhpUnitVersion()
    {
        return call_user_func([$this->phpunitVersionClass, 'id']);
    }

    /**
     * Detects the PHPUnit templates dir
     *
     * @return string|null
     */
    private function getPhpunitTemplatesDir()
    {
        $phpunitLocations = [
            __DIR__ . '/../../',
            __DIR__ . '/../vendor/',
        ];

        $phpunitRelativePath = '/phpunit/phpunit/src/Framework/MockObject/Generator';

        foreach ($phpunitLocations as $prefix) {
            $possibleDirs = [
                $prefix . $phpunitRelativePath . '/templates',
                $prefix . $phpunitRelativePath,
            ];

            foreach ($possibleDirs as $dir) {
                if (is_dir($dir)) {
                    return realpath($dir);
                }
            }
        }
    }

    /**
     * Clones original template with the wrapper
     *
     * @param string $templateFile Template filename
     * @param string $customTemplateFile Custom template filename
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    private function createCustomTemplateFile(string $templateFile, string $customTemplateFile)
    {
        $template = file_get_contents($templateFile);

        if (($start = strpos($template, $this->openInvocation)) !== false &&
            ($end = strpos($template, $this->closeFunc, $start)) !== false
        ) {
            $template = substr_replace($template, $this->closeFunc, $end, 0);
            $template = substr_replace($template, $this->openWrapper, $start, 0);

            if ($file = fopen($customTemplateFile, 'w+')) {
                fputs($file, $template);
                fclose($file);
            }
        }
    }
}
