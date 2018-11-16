<?php

use Fiedsch\Data\ArrayRecordCreator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayRecordCreatorTest
 */
class ArrayRecordCreatorTest extends TestCase
{

    /**
     * @var ArrayRecordCreator
     */
    protected $creator;

    public function setUp() {
        $this->creator = new ArrayRecordCreator(['foo','bar','baz']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAccessUndefinedColumn()
    {
        // column 'fred' was not present in the constructor's column names array
        $this->creator->fred = '1';
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetNonscalarValue()
    {
        // columns con only contain scalar values
        $this->creator->foo = [1,2,3];
    }

    public function testGetRecord()
    {
        $this->assertEquals([null,null,null], $this->creator->getRecord());
        $this->creator->foo = 1;
        $this->assertEquals([1,null,null], $this->creator->getRecord());
        $this->creator->baz = 2;
        $this->assertEquals([1,null,2], $this->creator->getRecord());
        $this->creator->bar = 3;
        $this->assertEquals([1,3,2], $this->creator->getRecord());
    }

    public function testGetColumn()
    {
        $this->creator->foo = 1;
        $this->assertEquals(1, $this->creator->foo);
    }

    public function testReset()
    {
        $this->creator->foo = 1;
        $this->creator->reset();
        $this->assertEquals([null,null,null], $this->creator->getRecord());
    }

    public function testColumnNameTypes()
    {
        $creator = new ArrayRecordCreator(['col042', 'f5.6', 'normal_name']);
        $creator->col042 = '42';
        $creator->normal_name = 'normal';

        // $creator->f5.6 = 'F5.6';
        // ^^^ will not work as it is invalid PHP code
        // so we have to use this:
        $colname = 'f5.6';
        $creator->$colname = 'F5.6';
        $this->assertEquals(['42','F5.6','normal'], $creator->getRecord());
        // or this:
        $creator->__set('f5.6', 'F5.6');
        $this->assertEquals(['42','F5.6','normal'], $creator->getRecord());
    }

}