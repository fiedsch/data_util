<?php

use Fiedsch\Data\Listmanager;
use PHPUnit\Framework\TestCase;

/**
 * Class ListmanagerTest
 */
class ListmamanagerTest extends TestCase
{
    public function testGetSetData(): void
    {
        $listA = ['a','b','c'];
        $listB = ['c','d','e'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->getData(), $listA);
        $manager->setData($listB);
        $this->assertEquals($manager->getData(), $listB);
    }
    public function testWithout(): void
    {
        $listA = ['a','b','c'];
        $listB = ['c','d','e'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->without($listB), ['a', 'b']);
    }

    public function testIntersect(): void
    {
        $listA = ['a','b','c'];
        $listB = ['c','d','e'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->intersect($listB), ['c']);
        $this->assertEquals($manager->intersect($listA), $listA);
        $listC = ['x','y','z'];
        $this->assertEquals($manager->intersect($listC), []);
    }

    public function testUnion(): void
    {
        $listA = ['a','b','c'];
        $listB = ['c','d','e'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->union($listB), ['a','b','c','c','d','e']);
        $this->assertEquals($manager->union($listA), array_merge($listA,$listA));
    }

    public function testUnique(): void
    {
        $listA = ['a','b','c','c','b'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->unique(), ['a','b','c']);
    }

    public function testDuplicates(): void
    {
        $listA = ['a','b','a','a','c'];
        $manager = new Listmanager($listA);
        $this->assertEquals($manager->duplicates(), ['a','a']);
    }

    public function testReindex(): void
    {
        $listA = [1=>'a',3=>'b',4=>'a',7=>'a',11=>'c'];
        $this->assertEquals(Listmanager::reindex($listA), ['a','b','a','a','c']);
    }

    public function testFitCase(): void
    {
        $listA = ['a','b','A','B'];
        $this->assertEquals(Listmanager::fitCase($listA), ['a','b','A','B']);
        $this->assertEquals(Listmanager::fitCase($listA, Listmanager::CASE_ASIS), ['a','b','A','B']);
        $this->assertEquals(Listmanager::fitCase($listA, Listmanager::CASE_LOWER), ['a','b','a','b']);
        $this->assertEquals(Listmanager::fitCase($listA, Listmanager::CASE_UPPER), ['A','B','A','B']);
    }

    public function testToLowerCase(): void
    {
        $listA = ['a','B','A','a','C','@','Μ'];
        // Note Μ (above) is the unicode upper case version of μ which looks
        // like the  unicode upper case version of m but (of course) is a
        // different caracter!
        $this->assertEquals(Listmanager::toLowerCase($listA), ['a','b','a','a','c','@','μ']);
    }

    public function testToUpperCase(): void
    {
        $listA = ['a','B','A','a','C','@','µ'];
        // Note Μ (below) is the unicode upper case version of μ which looks
        // like the  unicode upper case version of m but (of course) is a
        // different character!
        $this->assertEquals(Listmanager::toUpperCase($listA), ['A','B','A','A','C','@','Μ']);
    }

}
