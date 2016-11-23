# Utilities 

PHP classes and helpers that might be helpful when working with data files, variable name lists, etc. 
 
 * `Data\Helper` provides some static helper functions

 
## Usage

```php
<?php
require '/path/to/vendor/autoload.php';

use Fiedsch\Data\Helper;
use Fiedsch\Data\Listmanager;

// use Helper::expandExpression() et al.
// use Listmanager to perform operations on lists (arrays) 
// use ArrayRecordCreator to create data records
```


## Examples


### Create a list of variable names  

Create a list of variable names that might make it easier to write code in your favourite software for 
statistical analysis.
 
```php
print Helper::getExpression('a001', 'a111'); 
// 'a{001,111}' 

Helper::expandExpression('a{001,101}'); 
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
 
 