<?php

use Fiedsch\Data\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Class HelperTest
 */
class HelperTest extends TestCase
{

    /**
     * If the parameters do not "match"---meaning that it is impossible to create
     * an expression from them---an exception has to be thrown.
     *
     * @expectedException \RuntimeException
     */
    public function testGetExpressionWithNoMatchWrongCommonParts()
    {
        Helper::getExpression('a_1', 'z_1');
    }

    /**
     * If the parameters do not "match"---meaning that it is impossible to create
     * an expression from them---an exception has to be thrown.
     *
     * @expectedException \RuntimeException
     */
    public function testGetExpressionWithNoMatchCasDifferent()
    {
    Helper::getExpression("a1", "A1");
    }

    /**
     * Test various inputs
     */
    public function testGetExression()
    {
        $this->assertEquals('1', Helper::getExpression("1", "1"));
        $this->assertEquals('{1,2}', Helper::getExpression("1", "2"));
        $this->assertEquals('{1,111}', Helper::getExpression("1", "111"));
        $this->assertEquals('a1', Helper::getExpression("a1", "a1"));
        $this->assertEquals('a{1,2}', Helper::getExpression("a1", "a2"));
        $this->assertEquals('a{1,2}', Helper::getExpression("a1", "a2"));
        $this->assertEquals('a{1,111}', Helper::getExpression("a1", "a111"));
        $this->assertEquals('a_{1,2}', Helper::getExpression("a_1", "a_2"));
        $this->assertEquals('a_{1,111}', Helper::getExpression("a_1", "a_111"));
        $this->assertEquals('1b', Helper::getExpression("1b", "1b"));
        $this->assertEquals('{1,2}b', Helper::getExpression("1b", "2b"));
        $this->assertEquals('{1,111}b', Helper::getExpression("1b", "111b"));
        $this->assertEquals('{1,2}_b', Helper::getExpression("1_b", "2_b"));
        $this->assertEquals('{1,111}_b', Helper::getExpression("1_b", "111_b"));
        $this->assertEquals('a_{1,2}b', Helper::getExpression("a_1b", "a_2b"));
        $this->assertEquals('a_{1,111}b', Helper::getExpression("a_1b", "a_111b"));
        $this->assertEquals('a_{1,2}b', Helper::getExpression("a_1b", "a_2b"));
        $this->assertEquals('a_{1,111}b', Helper::getExpression("a_1b", "a_111b"));
        $this->assertEquals('a_{1,2}_b', Helper::getExpression("a_1_b", "a_2_b"));
        $this->assertEquals('a_{1,111}_b', Helper::getExpression("a_1_b", "a_111_b"));
        $this->assertEquals('a_1_{1,999}_x_100', Helper::getExpression("a_1_1_x_100", "a_1_999_x_100"));
        $this->assertEquals('a_1_1', Helper::getExpression("a_1_1", "a_1_1"));
        $this->assertEquals('a_11_x_100', Helper::getExpression("a_11_x_100", "a_11_x_100"));
        $this->assertEquals('a{01,03}', Helper::getExpression("a01", "a03"));
        $this->assertEquals('a{01,011}', Helper::getExpression("a01", "a011"));
    }

    /**
     *
     */
    public function testExpandExpression()
    {

        $this->assertEquals(24, count(Helper::expandExpression('_anfang{1,3}_mitte_{1,2}_ende_{1,4}')));
        $this->assertEquals(48, count(Helper::expandExpression('{0,1}_anfang{1,3}_mitte_{1,2}_ende_{1,4}')));

        $this->assertEquals([ ], Helper::expandExpression(''));
        $this->assertEquals([ 'a1' ], Helper::expandExpression('a1'));
        $this->assertEquals([ 'a1' ], Helper::expandExpression('a{1,1}'));
        $this->assertEquals([ 'a1', 'a2', 'a3' ], Helper::expandExpression('a{1,3}'));
        $this->assertEquals([ 'a01', 'a02', 'a03' ], Helper::expandExpression('a0{1,3}'));
        $this->assertEquals([ 'a01', 'a02', 'a03' ], Helper::expandExpression('a{01,03}'));
        $this->assertEquals([ '1b', '2b', '3b' ], Helper::expandExpression('{1,3}b'));
        $this->assertEquals([ 'a1_1', 'a1_2', 'a2_1', 'a2_2', 'a3_1', 'a3_2' ], Helper::expandExpression('a{1,3}_{1,2}'));
        $this->assertEquals([ '1a1_1', '1a1_2', '1a2_1', '1a2_2', '2a1_1', '2a1_2', '2a2_1', '2a2_2' ], Helper::expandExpression('{1,2}a{1,2}_{1,2}'));

        $this->assertEquals(['a_001', 'a_002','a_003','a_004' ], Helper::expandExpression("a_{001,004}"));
        // is the fact that 'a_{001,004}' yields the same result as 'a_{001,4}' a bug or a feature?
        $this->assertEquals(['a_001', 'a_002','a_003','a_004' ], Helper::expandExpression("a_{001,4}"));
        // same here:
        $this->assertEquals(Helper::expandExpression("a_{001,011}"), Helper::expandExpression("a_{001,11}"));
        $this->assertEquals(['a_009','a_010','a_011' ], Helper::expandExpression("a_{009,11}"));


        //print_r(Helper::expandExpression("a_{001,4}"));

        //print_r(Helper::expandExpression('image{00001,00099}.jpg'));
        
    }

    /**
     *
     */
    public function testColumnIndex()
    {
        $this->assertEquals(0,  Helper::columnIndex('A'));
        $this->assertEquals(1,  Helper::columnIndex('B'));
        $this->assertEquals(25, Helper::columnIndex('Z'));
        $this->assertEquals(26, Helper::columnIndex('AA'));
        $this->assertEquals(27, Helper::columnIndex('AB'));
        $this->assertEquals(51, Helper::columnIndex('AZ'));
        $this->assertEquals(52, Helper::columnIndex('BA'));
    }


}