<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\CreateCollectionEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;
use Doctrine\MongoDB\GridFS;

class DatabaseEventsTest extends TestCase
{
    private $connection;
    private $eventManager;
    private $mongoDB;
    private $configuration;

    public function setUp()
    {
        $this->connection = $this->getMockConnection();
        $this->eventManager = $this->getMockEventManager();
        $this->mongoDB = $this->getMockMongoDB();
        $this->configuration = $this->getMockConfiguration();
    }

    public function testCreateCollection()
    {
        $name = 'collection';
        $options = ['capped' => false, 'size' => 0, 'max' => 0];
        $result = $this->getMockCollection();

        $db = $this->getMockDatabase(['doCreateCollection' => $result]);

        $this->expectEvents([
            [Events::preCreateCollection, new CreateCollectionEventArgs($db, $name, $options)],
            [Events::postCreateCollection, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->createCollection($name, $options));
    }

    public function testDrop()
    {
        $result = ['dropped' => 'databaseName', 'ok' => 1];

        $this->mongoDB->expects($this->once())
            ->method('drop')
            ->will($this->returnValue($result));

        $db = new Database($this->connection, $this->mongoDB, $this->eventManager, $this->configuration);

        $this->expectEvents([
            [Events::preDropDatabase, new EventArgs($db)],
            [Events::postDropDatabase, new EventArgs($db)],
        ]);

        $this->assertSame($result, $db->drop());
    }

    public function testGetDBRef()
    {
        $reference = ['$ref' => 'collection', '$id' => 1];
        $result = ['_id' => 1];

        $db = $this->getMockDatabase(['doGetDBRef' => $result]);

        $this->expectEvents([
            [Events::preGetDBRef, new EventArgs($db, $reference)],
            [Events::postGetDBRef, new MutableEventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->getDBRef($reference));
    }

    public function testGetGridFS()
    {
        $prefix = 'fs';
        $result = $this->getMockGridFS();

        $db = $this->getMockDatabase(['doGetGridFS' => $result]);

        $this->expectEvents([
            [Events::preGetGridFS, new EventArgs($db, $prefix)],
            [Events::postGetGridFS, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->getGridFS());
    }

    public function testSelectCollection()
    {
        $name = 'collection';
        $result = $this->getMockCollection();

        $db = $this->getMockDatabase(['doSelectCollection' => $result]);

        $this->expectEvents([
            [Events::preSelectCollection, new EventArgs($db, $name)],
            [Events::postSelectCollection, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->selectCollection($name));
    }

    /**
     * Expect events to be dispatched by the event manager in the given order.
     *
     * @param array $events Tuple of event name and dispatch argument
     */
    private function expectEvents(array $events)
    {
        /* Each event should be a tuple consisting of the event name and the
         * dispatched argument (e.g. EventArgs).
         *
         * For each event, expect a call to hasListeners() immediately followed
         * by a call to dispatchEvent(). The dispatch argument is passed as-is
         * to with(), so constraints may be used (e.g. callback).
         */
        foreach ($events as $i => $event) {
            $this->eventManager->expects($this->at($i * 2))
                ->method('hasListeners')
                ->with($event[0])
                ->will($this->returnValue(true));

            $this->eventManager->expects($this->at($i * 2 + 1))
                ->method('dispatchEvent')
                ->with($event[0], $event[1]);
        }
    }

    /**
     * @return Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $methods
     * @return Database|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockDatabase(array $methods = [])
    {
        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->setConstructorArgs([$this->connection, $this->mongoDB, $this->eventManager, $this->configuration])
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $returnValue) {
            $db->expects($this->once())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $db;
    }

    /**
     * @return EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEventManager()
    {
        return $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return GridFS|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockGridFS()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\GridFS')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \MongoDB|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMongoDB()
    {
        return $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConfiguration()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
