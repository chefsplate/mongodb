<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\LoggableDatabase;

class LoggableDatabaseTest extends TestCase
{
    const databaseName = 'databaseName';

    public function testLog()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $db = $this->getTestLoggableDatabase($loggerCallable);
        $db->log(['test' => 'test']);

        $this->assertEquals(['db' => self::databaseName, 'test' => 'test'], $called);
    }

    private function getTestLoggableDatabase($loggerCallable)
    {
        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \MongoDB|\PHPUnit_Framework_MockObject_MockObject $mongoDB */
        $mongoDB = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDB->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::databaseName));

        /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Configuration|\PHPUnit_Framework_MockObject_MockObject $configuration */
        $configuration = $this->getMockBuilder('Doctrine\MongoDB\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableDatabase(
            $connection,
            $mongoDB,
            $eventManager,
            $configuration,
            $loggerCallable
        );
    }
}
