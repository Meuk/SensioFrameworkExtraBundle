<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * @Cache()
 * @Cache()
 */
#[Cache()]
#[Cache()]
class FooControllerMultipleCacheAtClass
{
    public function barAction()
    {
    }
}
