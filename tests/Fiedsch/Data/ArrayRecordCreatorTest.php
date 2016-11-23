<?php

use Fiedsch\Data\ArrayRecordCreator;

/**
 * Class ArrayRecordCreatorTest
 */
class ArrayRecordCreatorTest extends PHPUnit_Framework_TestCase
{

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
        // column 'fred' was not present in the constructor's column names array
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

}