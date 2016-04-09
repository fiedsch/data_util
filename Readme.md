# Utilities 

PHP classes and helpers that might be helpful when working with data files, variable name lists, etc. 
 
 * `Data\Helper` provides some static helper functions

 
## Usage

```php
<?php
require '/path/to/vendor/autoload.php';

use Fiedsch\Data\Helper;

// use Helper::expandExpression() et al.
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
