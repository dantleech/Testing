<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Tests\Phpunit;

use Symfony\Cmf\Component\Testing\Phpunit\DatabaseTestListener;

class DatabaseTestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $listener;

    private $process;

    private static $i;

    protected function setUp()
    {
        $this->listener = new DatabaseTestListener($this->getProcess());
        self::$i = 0;
    }

    public function testPhpcrTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('phpcr');

        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop', '--force']);
        $this->assertProcessExecuted(['doctrine:phpcr:repository:init']);

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[PHPCR]', ob_get_clean());
    }

    public function testFallsBackToOldInitDbalCommand()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('phpcr');

        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop', '--force'], false);
        $this->assertProcessExecuted(['doctrine:phpcr:init:dbal', '--drop'], true);
        $this->assertProcessExecuted(['doctrine:phpcr:repository:init']);

        ob_start();
        $this->listener->startTestSuite($suite);
        ob_end_clean();
    }

    public function testOrmTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('orm');

        $this->assertProcessExecuted(['doctrine:schema:drop', '--env=orm', '--force']);
        $this->assertProcessExecuted(['doctrine:database:create', '--env=orm']);
        $this->assertProcessExecuted(['doctrine:schema:create', '--env=orm']);

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[ORM]', ob_get_clean());
    }

    public function testUnknownTestSuite()
    {
        $suite = $this->createMock('PHPUnit_Framework_TestSuite');
        $suite->expects($this->any())
            ->method('getName')
            ->willReturn('not orm or phpcr tests');

        $this->getProcess()->expects($this->never())->method('setCommandLine');

        ob_start();
        $this->listener->startTestSuite($suite);

        $this->assertContains('[not orm or phpcr tests]', ob_get_clean());
    }

    protected function assertProcessExecuted(array $arguments, $successfull = true)
    {
        $process = $this->getProcess();

        $process
            ->expects($this->at(self::$i++))
            ->method('setCommandLine')
            ->with($this->equalTo($arguments))
            ->will($this->returnSelf());

        $process->expects($this->any())->method('run');

        $process->expects($this->any())
            ->method('isSuccessful')
            ->willReturn($successfull);
    }

    protected function getProcess()
    {
        if (null === $this->process) {
            $this->process = $this->createMock('Symfony\Component\Process\Process');
        }

        return $this->process;
    }
}
