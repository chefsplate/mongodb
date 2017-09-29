<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Configuration;
use MongoGridFS;
use MongoGridFSFile;
use Doctrine\MongoDB\LoggableGridFS;
use Doctrine\MongoDB\Database;
use Doctrine\Common\EventManager;

class LoggableGridFSTest extends TestCase
{
    const COLLECTION_NAME = 'collectionName';
    const DATABASE_NAME = 'databaseName';

    /** @var \PHPUnit_Framework_MockObject_MockObject|MongoGridFS */
    private $mongoGridFS;

    public function testLog()
    {
        $logResult = false;
        $loggableGridFS = $this->getLoggableGridFS($this->getLoggerCallable($logResult));
        $loggableGridFS->log(['test' => 'test']);

        $expected = [
            'collection' => self::COLLECTION_NAME,
            'db' => self::DATABASE_NAME,
            'test' => 'test'
        ];

        $this->assertEquals($expected, $logResult);
    }

    public function testStoreFileLog()
    {
        $logResult = false;

        $loggableGridFS = $this->getLoggableGridFS($this->getLoggerCallable($logResult));

        $mongoGridFSFile = $this->getMockBuilder(MongoGridFSFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mongoGridFS->expects($this->any())
            ->method('get')
            ->will($this->returnValue($mongoGridFSFile));

        $document = $document = ['foo' => 'bar'];
        $loggableGridFS->storeFile(__FILE__, $document, array('foo' => 'bar'));

        $expectedLog = [
            'storeFile' => true,
            'options' => array('foo' => 'bar'),
            'db' => self::DATABASE_NAME,
            'count' => 1,
            'collection' => self::COLLECTION_NAME,
        ];

        $this->assertEquals($expectedLog, $logResult);
    }

    private function getLoggerCallable(&$called)
    {
        return function($msg) use (&$called) {
            $called = $msg;
        };
    }

    private function getLoggableGridFS($loggerCallable)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::DATABASE_NAME));

        $this->mongoGridFS = $this->getMockBuilder(MongoGridFS::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mongoGridFS->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::COLLECTION_NAME));

        /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMockBuilder(EventManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Configuration|\PHPUnit_Framework_MockObject_MockObject $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableGridFS($database, $this->mongoGridFS, $eventManager, $configuration, $loggerCallable);
    }
}
