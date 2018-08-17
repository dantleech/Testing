<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Fixtures;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TestTestCase extends BaseTestCase
{
    public function setKernel(KernelInterface $kernel)
    {
        static::$kernel = $kernel;
    }

    protected static function createKernel(array $options = [])
    {
        if (null === static::$kernel) {
            return parent::createKernel($options);
        }

        return static::$kernel;
    }
}
