<?php

if (! interface_exists(\PHPUnit\Framework\MockObject\Matcher\Invocation::class)) {
    class_alias(
        \PHPUnit_Framework_MockObject_Matcher_Invocation::class,
        \PHPUnit\Framework\MockObject\Matcher\Invocation::class
    );
}

if (! interface_exists(\PHPUnit\Framework\MockObject\Invocation::class)) {
    class_alias(
        \PHPUnit_Framework_MockObject_Invocation::class,
        \PHPUnit\Framework\MockObject\Invocation::class
    );
}

if (! interface_exists(\PHPUnit\Framework\MockObject\MockObject::class)) {
    class_alias(
        \PHPUnit_Framework_MockObject_MockObject::class,
        \PHPUnit\Framework\MockObject\MockObject::class
    );
}

if (! class_exists(\PHPUnit\Framework\MockObject\Builder\InvocationMocker::class)) {
    class_alias(
        \PHPUnit_Framework_MockObject_Builder_InvocationMocker::class,
        \PHPUnit\Framework\MockObject\Builder\InvocationMocker::class
    );
}
