<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerMultipleCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerMultipleCacheAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerParamConverterAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerParamConverterAttributeAtClassAndMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ControllerListenerTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->request = $this->createRequest();

        // trigger the autoloading of the @Cache annotation
        class_exists(Cache::class);
    }

    protected function tearDown(): void
    {
        $this->listener = null;
        $this->request = null;
    }

    public function testCacheAnnotationAtMethod()
    {
        $controller = new FooControllerCacheAtMethod();

        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtMethod()
    {
        $controller = new FooControllerCacheAttributeAtMethod();

        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClass()
    {
        $controller = new FooControllerCacheAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClass::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtClass()
    {
        $controller = new FooControllerCacheAttributeAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClass::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClassAndMethod()
    {
        $controller = new FooControllerCacheAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());

        $this->event = $this->getFilterControllerEvent([$controller, 'bar2Action'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtClassAndMethod()
    {
        $controller = new FooControllerCacheAttributeAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAttributeAtClassAndMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());

        $this->event = $this->getFilterControllerEvent([$controller, 'bar2Action'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAttributeAtClassAndMethod::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testMultipleAnnotationsOnClassThrowsExceptionUnlessConfigurationAllowsArray()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Multiple "cache" annotations are not allowed');

        $controller = new FooControllerMultipleCacheAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);
    }

    public function testMultipleAnnotationsOnMethodThrowsExceptionUnlessConfigurationAllowsArray()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Multiple "cache" annotations are not allowed');

        $controller = new FooControllerMultipleCacheAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);
    }

    public function testMultipleParamConverterAnnotationsOnMethod()
    {
        $paramConverter = new ParamConverter([]);
        $controller = new FooControllerParamConverterAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[0]);
        $this->assertEquals('test', $annotations[0]->getName());

        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[1]);
        $this->assertEquals('test2', $annotations[1]->getName());

        $this->assertCount(2, $annotations);
    }

    /**
     * @requires PHP 8.0
     */
    public function testMultipleParamConverterAttributesOnMethod()
    {
        $paramConverter = new ParamConverter([]);
        $controller = new FooControllerParamConverterAttributeAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[0]);
        $this->assertEquals('test', $annotations[0]->getName());

        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[1]);
        $this->assertEquals('test2', $annotations[1]->getName());

        $this->assertCount(2, $annotations);
    }

    private function createRequest(Cache $cache = null)
    {
        return new Request([], [], [
            '_cache' => $cache,
        ]);
    }

    private function getFilterControllerEvent($controller, Request $request)
    {
        $mockKernel = $this->getMockForAbstractClass(\Symfony\Component\HttpKernel\Kernel::class, ['', '']);

        return new ControllerEvent($mockKernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function getReadedCache()
    {
        return $this->request->attributes->get('_cache');
    }
}
