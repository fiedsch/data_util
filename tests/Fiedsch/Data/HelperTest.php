<?php

declare(strict_types=1);

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
     */
    public function testGetExpressionWithNoMatchWrongCommonParts(): void
    {
        $this->expectException(\RuntimeException::class);
        Helper::getExpression('a_1', 'z_1');
    }

    /**
     * If the parameters do not "match"---meaning that it is impossible to create
     * an expression from them---an exception has to be thrown.
     */
    public function testGetExpressionWithNoMatchCasDifferent(): void
    {
        $this->expectException(\RuntimeException::class);
        Helper::getExpression("a1", "A1");
    }

    /**
     * Test various inputs
     */
    public function testGetExpression(): void
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
    public function testExpandExpression(): void
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
    public function testColumnIndex(): void
    {
        $this->assertEquals(0,  Helper::columnIndex('A'));
        $this->assertEquals(1,  Helper::columnIndex('B'));
        $this->assertEquals(25, Helper::columnIndex('Z'));
        $this->assertEquals(26, Helper::columnIndex('AA'));
        $this->assertEquals(27, Helper::columnIndex('AB'));
        $this->assertEquals(51, Helper::columnIndex('AZ'));
        $this->assertEquals(52, Helper::columnIndex('BA'));
    }

    /**
     *
     */
    public function testColumnName(): void
    {
        $this->assertEquals('A', Helper::columnName(0));
        $this->assertEquals('B', Helper::columnName(1));
        $this->assertEquals('Z', Helper::columnName(25));
        $this->assertEquals('AA', Helper::columnName(26));
        $this->assertEquals('AB', Helper::columnName(27));
        $this->assertEquals('BA', Helper::columnName(52));
    }

    /**
     * Test f(f^-1(x)) = x
     */
    public function testColumnNameOfColumnIndex(): void
    {
        for ($i = 0; $i < 256; $i++) {
            $this->assertEquals(Helper::columnIndex(Helper::columnName($i)),  $i);
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testColumnNameThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        Helper::columnName(-1);
    }

    /**
     *
     */
    public function testPrependAndRemapContinuous(): void
    {
        $base = ['x1' => 'A', 'x2' => 'B', 'x3' => 'C'];
        $add = ['x4', 'x5'];
        $this->assertEquals(json_encode(['x4' => 'A', 'x5' => 'B', 'x1' => 'C', 'x2' => 'D', 'x3' => 'E']), json_encode(Helper::prependAndRemap($base, $add)));
    }

    /**
     *
     */
    public function testPrependAndRemapWithGaps(): void
    {
        $base = ['x1' => 'A', 'x2' => 'C', 'x3' => 'E'];
        $add = ['x4', 'x5'];
        $this->assertEquals(json_encode(['x4' => 'A', 'x5' => 'B', 'x1' => 'C', 'x2' => 'E', 'x3' => 'G']), json_encode(Helper::prependAndRemap($base, $add)));
    }

    /**
     *
     */
    public function testAppendAndRemapThrowsException(): void
    {
        // has to throw an Exception if keys are not unique
        $this->expectException(\RuntimeException::class);
        $base = ['x1' => 'A', 'x2' => 'B', 'x3' => 'C'];
        $add = ['x2'];
        Helper::prependAndRemap($base, $add);
    }


}
