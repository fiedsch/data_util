<?php

declare(strict_types=1);

/**
 * @package    data_util
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/data_util
 */
namespace Fiedsch\Data;

/**
 * Class ArrayRecordCreator
 * @package Fiedsch\Data
 *
 * Helps creating a data record for a given "schema" which is an array
 * of column names can be the columns of a CSV file or any other ordered
 * array structure.
 *
 * Best described by an Example:
 * <code>
 * $creator = new ArrayRecordCreator(['foo','bar','baz']);
 * // add values in arbitrary order
 * $creator->foo = '1';
 * $creator->baz = '2';
 * $creator->bar = '3';
 * $record = $creator->getRecord(); // [1, 3, 2]
 *
 * $creator->reset();
 * $creator->foo = 'FOO';
 * $record = $creator->getRecord(); // ['FOO', null, null]
 * </code>
 */
class ArrayRecordCreator
{
    /**
     * @var array
     */
    protected $record;

    /**
     * @var array
     */
    protected $colnames;

    /**
     * @var array
     */
    protected $colpositions;
    /**
     * ArrayRecordCreator constructor.
     * @param array $colnames an array of column names that define the
     *              order of the values in the result we are building.
     */
    public function __construct(array $colnames)
    {
        $this->colnames = $colnames;
        $this->colpositions = array_flip($this->colnames);
        $this->reset();
    }

    /**
     * reset the internal data structures. Use to start building
     * a new record.
     */
    public function reset()
    {
        $this->record = array_fill(0, count($this->colnames), null);
    }

    /**
     * Return the current data record (numerically indexed array)
     * @return array
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Return the current data record in an array with the corresponding column names as keys
     * @return array
     */
    public function getMappedRecord(): array
    {
        $result = [];
        foreach ($this->colnames as $i => $name) {
            $result[$name] = $this->record[$i];
        }

        return $result;
    }


    /**
     * @param string $name column name
     * @param mixed $value value to be stored in that column
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->colpositions)) {
            throw new \RuntimeException("column '$name' is not defined");
        }
        if (!is_scalar($value)) {
            throw new \RuntimeException("columns can only contain scalar values");
        }

        $this->record[$this->colpositions[$name]] = $value;
    }

    /**
     * @param string $name column name
     * @return mixed $value value which is stored in that column
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->colpositions)) {
            throw new \RuntimeException("column '$name' is not defined");
        }
        return $this->record[$this->colpositions[$name]];
    }

}
