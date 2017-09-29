<?php namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Database;

class CollectionWriteRetryTest extends TestCase
{
    /** @var Database|\PHPUnit_Framework_MockObject_MockObject */
    private $database;

    /** @var \MongoCollection|\PHPUnit_Framework_MockObject_MockObject */
    private $mongo_collection;

    /** @var Configuration */
    private $configuration;

    /** @var CollectionWriteRetryTestDouble */
    private $collection;

    protected function setUp()
    {
        parent::setUp();

        $this->database = $this->getMockDatabase();
        $this->mongo_collection = $this->getMockMongoCollection();
        $event_manager = new EventManager();

        $this->configuration = new Configuration();

        $this->collection = new CollectionWriteRetryTestDouble(
            $this->database,
            $this->mongo_collection,
            $event_manager,
            $this->configuration
        );
    }

    /**
     * @covers Collection::retryInsert()
     */
    public function testRetryInsertClosureReturnedImmediatelyIfRetriesLessThanOne()
    {
        $this->configuration->setNumRetryWrites(false);

        $doc = ['x' => 1];

        try {
            $this->collection->insert($doc);
        } catch (\MongoConnectionException $exception) {}

        $this->assertEquals(1, $this->collection->times_attempted);

        $this->configuration->setNumRetryWrites(-123);

        try {
            $this->collection->insert($doc);
        } catch (\MongoConnectionException $exception) {}

        $this->assertEquals(1, $this->collection->times_attempted);
    }

    /**
     * @covers Collection::retryInsert()
     */
    public function testRetryInsertOnlyIgnoresDuplicateKeyExceptionsForDefaultIdIndex()
    {
        $this->configuration->setNumRetryWrites(1);
        $doc = ['x' => 1];

        $correct_exception = new \MongoDuplicateKeyException('This should pass: _id_ dup key: { : 1 }');
        $incorrect_exception = new \MongoDuplicateKeyException('This should fail: _id_field_1 dup key: { : 1 }');

        $this->collection->exception = [new \MongoConnectionException(), $incorrect_exception];

        $last_caught_exception = null;

        try {
            $this->collection->insert($doc);
        } catch (\MongoDuplicateKeyException $exception) {
            $last_caught_exception = $exception;
            $this->collection->exception = [new \MongoConnectionException(), $correct_exception];

            try {
                $this->collection->insert($doc);
                $this->assertEquals(2, $this->collection->times_attempted);
            } catch (\MongoDuplicateKeyException $exception) {
                $this->fail("Duplicate key exception for only id should be successful on 1+ iterations.");
            }
        }

        $this->assertInstanceOf(\MongoDuplicateKeyException::class, $last_caught_exception);
    }

    /**
     * @covers Collection::retryInsert()
     */
    public function testRetryInsertOnlyIgnoresDuplicateKeyExceptionsBeyondFirstRetryIteration()
    {
        $this->configuration->setNumRetryWrites(2);

        $doc = ['x' => 1];

        $this->collection->exception = [
            new \MongoConnectionException(),
            new \MongoDuplicateKeyException('E11000 duplicate key error index: test. _id_ dup key: { : 1 }')
        ];
        try {
            $this->collection->insert($doc);
        } catch (\MongoDuplicateKeyException $exception) {
            $this->fail("Exception should not have been bubbled up; it should have been successful");
        }

        $this->assertEquals(2, $this->collection->times_attempted);
    }

    /**
     * @covers Collection::retryInsert()
     */
    public function testRetryInsertGeneratesIdIfDocumentHasNone()
    {
        $this->configuration->setNumRetryWrites(1);

        $doc = ['x' => 1];

        $this->assertFalse(isset($doc['_id']), "This test relies on the _id field of the doc being empty.");

        $this->collection->insert($doc);

        $this->assertTrue(isset($doc['_id']));
        $this->assertInstanceOf(\MongoId::class, $doc['_id']);
    }

    /**
     * @covers Collection::retryBatchInsert()
     */
    public function testBatchInsertGeneratesIdIfObjectsOrArraysHaveNone()
    {
        $this->configuration->setNumRetryWrites(1);

        $docs = [
            (object) ['x' => 1],
            ['y' => 1],
        ];

        $this->assertFalse(isset($docs[0]->_id));
        $this->assertFalse(isset($docs[1]['_id']));

        $this->collection->batchInsert($docs);

        $this->assertTrue(isset($docs[0]->_id));
        $this->assertInstanceOf(\MongoId::class, $docs[0]->_id);
        $this->assertTrue(isset($docs[1]['_id']));
        $this->assertInstanceOf(\MongoId::class, $docs[1]['_id']);
    }

    /**
     * @covers Collection::retryBatchInsert()
     */
    public function testRetryBatchInsertReturnsClosureImmediatelyIfRetriesLessThanOne()
    {
        $this->configuration->setNumRetryWrites(0);

        $docs = [
            (object) ['x' => 1],
            ['y' => 1],
        ];

        $this->collection->batchInsert($docs);
        $this->assertEquals(1, $this->collection->times_attempted);

        $this->configuration->setNumRetryWrites(-1000);

        $this->collection->batchInsert($docs);
        $this->assertEquals(1, $this->collection->times_attempted);
    }

    /**
     * @covers Collection::retryBatchInsert()
     */
    public function testRetryBatchInsertContinuesOnErrorIterationsBeyondTheFirst()
    {
        $this->configuration->setNumRetryWrites(1);

        $docs = [
            [
                'x' => 1,
            ],
            [
                'y' => 1,
            ]
        ];

        $this->collection->exception = [new \MongoConnectionException(), new \MongoDuplicateKeyException()];

        try {
            $this->collection->batchInsert($docs);
        } catch (\MongoDuplicateKeyException $exception) {}

        $this->assertEquals(2, $this->collection->times_attempted);
        $this->assertTrue(isset($this->collection->batch_insert_options['continueOnError']));
        $this->assertTrue($this->collection->batch_insert_options['continueOnError']);
    }

    /**
     * @covers Collection::retryBatchInsert()
     */
    public function testRetryBatchInsertRevertsContinueOnErrorOptionToPreviousValueWhenRetriesFinishSuccessfully()
    {
        $this->configuration->setNumRetryWrites(1);

        $docs = [
            [
                'x' => 1,
            ],
            [
                'y' => 1,
            ]
        ];

        $options = ['continueOnError' => 'test'];

        $this->collection->exception = [new \MongoConnectionException()];

        $this->collection->batchInsert($docs, $options);

        $this->assertEquals(2, $this->collection->times_attempted);
        $this->assertEquals($options, $this->collection->batch_insert_options);

        $this->collection->batchInsert($docs);

        $this->assertEquals(2, $this->collection->times_attempted);
        $this->assertFalse(isset($this->collection->batch_insert_options['continueOnError']));
    }

    /**
     * @covers Collection::retryIdempotentUpdate()
     */
    public function testRetryUpdateImmediatelyReturnsClosureIfNumberOfRetriesLessThanOne()
    {
        $this->configuration->setNumRetryWrites(0);

        $update = [ '$set' => [ 'field' => 'value' ] ];

        $this->collection->update([], $update);
        $this->assertEquals(1, $this->collection->times_attempted);

        $this->configuration->setNumRetryWrites(-1000);

        $this->collection->update([], $update);
        $this->assertEquals(1, $this->collection->times_attempted);
    }

    /**
     * @covers Collection::retryIdempotentUpdate()
     */
    public function testRetryUpdateImmediatelyReturnsClosureOnNonIdempotentOperations()
    {
        $this->configuration->setNumRetryWrites(5);

        $inc = [ '$inc' => [ 'field' => 1 ] ];
        $current_date = [ '$currentDate' => [ 'field' => [ '$type' => 'timestamp' ] ] ];
        $mul = [ '$mul' => [ 'field' => 2 ] ];
        $pop = [ '$pop' => [ 'field' => -1 ] ];
        $push = [ '$push' => [ 'field' => [ '$each' => [ 'value1', 'value2' ] ] ] ];
        $pushAll = [ '$pushAll' => [ 'value1', 'value2' ] ];
        $bit = [ 'field' => [ 'and' => 1 ] ];

        $disallowed_update_retry_operators = [
            $inc,
            $current_date,
            $mul,
            $pop,
            $push,
            $pushAll,
            $bit,
        ];

        foreach ($disallowed_update_retry_operators as $operator) {
            $this->collection->update([], $operator);

            $this->assertEquals(1, $this->collection->times_attempted);
        }
    }

    /**
     * @return Database|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockDatabase()
    {
        return $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \MongoCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMongoCollection()
    {
        return $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

class CollectionWriteRetryTestDouble extends Collection
{
    public $times_attempted;

    public $exception;

    public $batch_insert_options;

    protected function doInsert(array &$a, array $options)
    {
        $closure = $this->getTestRetryClosure();

        return $this->retryInsert($closure, $a);
    }

    protected function doBatchInsert(array &$a, array $options = [])
    {
        $options_to_pass = $options;

        $this->batch_insert_options =& $options_to_pass;

        $closure = $this->getTestRetryClosure();

        return $this->retryBatchInsert($closure, $options_to_pass, ...$a);
    }

    protected function doUpdate(array $query, array $newObj, array $options)
    {
        $closure = $this->getTestRetryClosure();

        return $this->retryIdempotentUpdate($closure, $newObj);
    }

    private function getTestRetryClosure()
    {
        $this->times_attempted = 0;

        $closure = function () {
            if (is_array($this->exception)) {
                foreach ($this->exception as $iteration => $exception) {
                    /** @var \Exception $exception */
                    /** @var int $iteration */
                    if ($this->times_attempted === $iteration) {
                        $this->times_attempted++;
                        throw $exception;
                    }
                }
            } else if ($this->exception instanceof \Exception) {
                throw $this->exception;
            }

            $this->times_attempted++;
            return true;
        };

        return $closure;
    }

    protected function sleepForMs($ms)
    {
        // Disabled so we don't end up delaying tests by significant time periods; this can be tested on it's own
        // since it's a singular unit
    }
}
