<?php

/**
 * Listmanager: Functions typically needed when working with Lists of data,
 * e.g. a list of email addresses ehere a common task is to find duplicates
 * in the list or where you have two lists (a target list and a blacklist)
 * and the task would be "find the entries from the target list that are
 * not blacklisted).
 *
 * @package    data_util
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/data_util
 */
namespace Fiedsch\Data;

class Listmanager
{
    /**
     * @var array
     */
    protected $data;
    
    const CASE_ASIS = 1;

    const CASE_LOWER = 2;

    const CASE_UPPER = 3;

    /**
     * @var int
     */
    protected $use_case;

    /**
     * Listmanager constructor.
     *
     * @param array $data
     * @param int $use_case
     */
    public function __construct(array $data, $use_case = self::CASE_ASIS)
    {
        $this->use_case = $use_case;
        $this->data = self::fitCase($data, $use_case);
    }

    /**
     * All entries in $this->data that are not contained in $other.
     *
     * @param array $other
     * @return array
     */
    public function without(array $other)
    {
        return self::reindex(array_diff($this->data, self::fitCase($other, $this->use_case)));
    }

    /**
     * Intersect $this->data with $other. The result only contains
     * the values that are contained in both lists.
     *
     * @param array $other
     * @return array
     */
    public function intersect(array $other)
    {
        return self::reindex(array_intersect($this->data, self::fitCase($other, $this->use_case)));
    }

    /**
     * Union. All entries that are contained in $this->data or $other.
     *
     * @param array $other
     * @return array
     */
    public function union(array $other)
    {
        return self::reindex(array_unique(array_merge($this->data, self::fitCase($other, $this->use_case))));
    }

    /**
     * $this->data without duplicates.
     *
     * @return array
     */
    public function unique()
    {
        return self::reindex(array_unique($this->data));
    }

    /**
     * Duplicates in $this->data. Note: the first occurrence of
     * an entry is not considered a duplicate and thus not contained
     * in the result. Example [1,2,1,2,2,1] yields [1,2,2,1].
     *
     * @return array
     */
    public function duplicates()
    {
        return self::reindex(array_diff_key($this->data, array_unique($this->data)));
    }

    /**
     * Reorganize $list such that the entries are indexed
     * from 0, 1, ...
     *
     * @param array $list
     * @return array
     */
    public static function reindex(array $list)
    {
        return array_values($list);
    }

    /**
     * @param array $data
     * @param int $use_case
     * @return array
     */
    public static function fitCase(array $data, $use_case = self::CASE_ASIS)
{
    switch ($use_case) {
        case self::CASE_ASIS: return $data; break;
        case self::CASE_LOWER: return self::toLowerCase($data); break;
        case self::CASE_UPPER: return self::toUpperCase($data); break;
        default: throw new \RuntimeException("invalid value for use_case: '$use_case'");
    }
}

    /**
    * Return the list where all entries are transformed to lower case.
    * @param array $list
    * @return array
    */
    public static function toLowerCase(array $list)
    {
        return array_map(function ($element) {
            return mb_strtolower($element);
        }, $list);
    }

    /**
     * Return the list where all entries are transformed to lower case.
     * @param array $list
     * @return array
     */
    public static function toUpperCase(array $list)
    {
        return array_map(function ($element) {
            return mb_strtoupper($element);
        }, $list);
    }

}