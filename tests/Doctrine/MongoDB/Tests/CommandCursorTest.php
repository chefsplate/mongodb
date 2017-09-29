<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\CommandCursor;
use Doctrine\MongoDB\Configuration;

class CommandCursorTest extends TestCase
{
    public function setUp()
    {
        if ( ! class_exists('MongoCommandCursor')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCommandCursor');
        }
    }

    public function testBatchSize()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('batchSize')
            ->with(10);

        $configuration = $this->getMockConfiguration();

        $commandCursor = new CommandCursor($mongoCommandCursor, $configuration);
        $this->assertSame($commandCursor, $commandCursor->batchSize(10));
    }

    public function testDead()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('dead')
            ->will($this->returnValue(true));

        $configuration = $this->getMockConfiguration();

        $commandCursor = new CommandCursor($mongoCommandCursor, $configuration);
        $this->assertTrue($commandCursor->dead());
    }

    public function testGetMongoCommandCursor()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();
        $configuration = $this->getMockConfiguration();
        $commandCursor = new CommandCursor($mongoCommandCursor, $configuration);
        $this->assertSame($mongoCommandCursor, $commandCursor->getMongoCommandCursor());
    }

    public function testInfo()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('info')
            ->will($this->returnValue(['info']));

        $configuration = $this->getMockConfiguration();

        $commandCursor = new CommandCursor($mongoCommandCursor, $configuration);
        $this->assertEquals(['info'], $commandCursor->info());
    }

    public function testTimeout()
    {
        if ( ! method_exists('MongoCommandCursor', 'timeout')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCommandCursor::timeout()');
        }

        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('timeout')
            ->with(1000);

        $configuration = $this->getMockConfiguration();

        $commandCursor = new CommandCursor($mongoCommandCursor, $configuration);
        $this->assertSame($commandCursor, $commandCursor->timeout(1000));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testTimeoutShouldThrowExceptionForOldDrivers()
    {
        if (method_exists('MongoCommandCursor', 'timeout')) {
            $this->markTestSkipped('This test is not applicable to drivers with MongoCommandCursor::timeout()');
        }

        $commandCursor = new CommandCursor($this->getMockMongoCommandCursor(), $this->getMockConfiguration());
        $commandCursor->timeout(1000);
    }

    /**
     * @return \MongoCommandCursor|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMongoCommandCursor()
    {
        return $this->getMockBuilder('MongoCommandCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConfiguration()
    {
        return $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
