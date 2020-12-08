<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

/**
 * Doctrine-specific ParamConverter with an easier syntax.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 * @Annotation
 */
#[\Attribute()]
class Entity extends ParamConverter
{
    public function setExpr($expr)
    {
        $options = $this->getOptions();
        $options['expr'] = $expr;

        $this->setOptions($options);
    }

    public function __construct(
        $values = [],
        string $expr = null,
        string $class = null,
        array $options = [],
        bool $isOptional = false,
        string $converter = null
    ) {
        if (isset($values[0])) {
            $values['value'] = $values[0];
            unset($values[0]);
        }

        $values['expr'] = $values['expr'] ?? $expr;

        parent::__construct($values, $class, $options, $isOptional, $converter);

        $this->setExpr($values['expr']);
    }
}
