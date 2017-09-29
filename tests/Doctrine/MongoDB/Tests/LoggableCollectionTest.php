<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\LoggableCollection;

class LoggableCollectionTest extends TestCase
{
    const collectionName = 'collectionName';
    const databaseName = 'databaseName';

    public function testLog()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $collection = $this->getTestLoggableCollection($loggerCallable);
        $collection->log(['test' => 'test']);

        $this->assertEquals(['collection' => self::collectionName, 'db' => self::databaseName, 'test' => 'test'], $called);
    }

    private function getTestLoggableCollection($loggerCallable)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Database $database */
        $database = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();

        $database->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::databaseName));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\MongoCollection $mongoCollection */
        $mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::collectionName));

        /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Configuration|\PHPUnit_Framework_MockObject_MockObject $configuration */
        $configuration = $this->getMockBuilder('Doctrine\MongoDB\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableCollection($database, $mongoCollection, $eventManager, $configuration, $loggerCallable);
    }
}
