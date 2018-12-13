# Utilities 

PHP classes and helpers that might be helpful when working with data files, variable name lists, etc. 
 
 * `Data\Helper` provides some static helper functions

 
## Usage

```php
<?php
require '/path/to/vendor/autoload.php';

use Fiedsch\Data\Helper;
use Fiedsch\Data\Listmanager;
use Fiedsch\Data\ArrayRecordCreator;

// use Helper::expandExpression() et al.
// use Listmanager to perform operations on lists (arrays) 
// use ArrayRecordCreator to create data records
// use Helper::columnIndex() and Helper::columnName() for column name to numerical index mappings
```


## Examples


### Create a list of variable names  

Create a list of variable names that might make it easier to write code in your favourite software for 
statistical analysis.
 
```php
print Helper::getExpression('a001', 'a111'); 
// 'a{001,111}' 

Helper::expandExpression('a{001,111}'); 
// array('a001', 'a002', 'a003', ..., 'a099', 'a100', ..., 'a111')

print "someFunction(" . join(',', Helper::expandExpression('a{001,101}')) . ");"; 
```


### Create a list of file names

```php
Helper::expandExpression('image{00001,00099}.jpg');
// array('image00001.jpg', ..., 'image00099.jpg') 
```
### Comparing Lists

Notice: result are a lists, not sets (see e.g. union()!)

```php
$listA = ['a','b','c'];
$listB = ['c','d','e'];
$manager = new Listmanager($listA);
$result = $manager->without($listB);   // ['a', 'b']
$result = $manager->intersect($listB); // ['c']
$result = $manager->union($listB);     // ['a','b','c','c','d','e']

$manager = new Listmanager(['a','b','c','c','b']);
$result = $manager->unique(); // ['a','b','c']

// find duplicates in a list
$list = ['a','b','a','a','c'];
$manager = new Listmanager($list);
$result = $manager->duplicates(); // ['a','a'] 
// $list[0] is considered unique, $list[2] and $list[3] are in the result
```

### Creating data records

```php
$creator = new ArrayRecordCreator(['foo','bar','baz']);
 // add values in arbitrary order
 $creator->foo = '1';
 $creator->baz = '2';
 $creator->bar = '3';
 $record = $creator->getRecord(); // [1, 3, 2]
 
 $creator->reset();
 $creator->foo = 'FOO';
 $record = $creator->getRecord(); // ['FOO', null, null]
```
 
 Combined usage with `Helper`

```php
 // create target columns 'col001' to 'col100'
 $creator = new ArrayRecordCreator(Helper::expandExpression('col{001,100}'));
 $creator->col042 = 'fourtytwo';
 // ...
```
 
 ### Working with data arrays read from a (CSV) file
 
 If you are working with data records stored in PHP arrays--e.g. when reading lines from 
 a CSV file--you might find it useful to access the entries by their "column name" rather
 than their numerical index. This is especially useful if the data originally "lives" in
 an Excel Spreadsheet where you have column names "A", "B", ... 
 
 To map  "A", "B", ... to the respective array indices 0, 1, ... you can use
 
```php
Helper::columnIndex("A"); // 0
Helper::columnIndex("B"); // 1
// ...
Helper::columnIndex("AQ"); // 42
```

The inverse funtion to `columnIndex()` is `columnName()` which might also be useful when
dealing with column name to array index mappings.

```php
Helper::columnName(0); // "A"
Helper::columnIndex(1); // "B"
// ...
Helper::columnIndex(42); // "AQ"
```

Lastly, if you have a mapping and create a new data file by prepending some data columns,
`prependAndRemap()` can help to create the mapping for the new file (that might serve as 
input for a next step of your data management process and hence also needs a mapping).
  