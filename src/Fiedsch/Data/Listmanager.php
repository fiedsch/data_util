<?php

/**
 * Listmanager: Functions typically needed when working with Lists of data,
 * e.g. a list of email addresses where a common task is to find duplicates
 * in the list or where you have two lists (a target list and a blacklist)
 * and the task would be "find the entries from the target list that are
 * not on an exclude-list").
 *
 * @package    data_util
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/data_util
 */
namespace Fiedsch\Data;

use LogicException;

class Listmanager
{
    protected array $data;

    /**
     * @const int leave as is when changing character case
     */
    const CASE_ASIS = 1;

    /**
     * @const int change to lower case
     */
    const CASE_LOWER = 2;

    /**
     * @const int change to upper case
     */
    const CASE_UPPER = 3;

    /**
     * @var int which case transformation is to be used
     */
    protected int $use_case;

    /**
     * Listmanager constructor.
     *
     * @param array $data the list to operate on
     * @param int $use_case transform all entries to the specified case
     */
    public function __construct(array $data, int $use_case = self::CASE_ASIS)
    {
        $this->use_case = $use_case;
        $this->setData($data);
    }

    /**
     * Set new data.
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = self::fitCase($data, $this->use_case);
    }

    /**
     * Return the data. Note the data has been transformed by
     * <code>Listmanager::fitCase()</code> in the constructor or
     * in <code>setData()</code>.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * All entries in $this->data that are not contained in $other.
     * @param array $other
     * @return array
     */
    public function without(array $other): array
    {
        return self::reindex(array_diff($this->data, self::fitCase($other, $this->use_case)));
    }

    /**
     * Intersect $this->data with $other. The result only contains
     * the values that are contained in both lists.
     * @param array $other
     * @return array
     */
    public function intersect(array $other): array
    {
        return self::reindex(array_intersect($this->data, self::fitCase($other, $this->use_case)));
    }

    /**
     * Union. All entries that are contained in $this->data or $other.
     * @param array $other
     * @return array
     */
    public function union(array $other): array
    {
        return self::reindex(array_merge($this->data, self::fitCase($other, $this->use_case)));
    }

    /**
     * $this->data without duplicates.
     * @return array
     */
    public function unique(): array
    {
        return self::reindex(array_unique($this->data));
    }

    /**
     * Duplicates in $this->data. Note: the first occurrence of
     * an entry is not considered a duplicate and thus not contained
     * in the result. Example [1,2,1,2,2,1] yields [1,2,2,1].
     * @return array
     */
    public function duplicates(): array
    {
        return self::reindex(array_diff_key($this->data, array_unique($this->data)));
    }

    /**
     * Reorganize $list such that the entries are indexed
     * from 0, 1, ...
     * @param array $list
     * @return array
     */
    public static function reindex(array $list): array
    {
        return array_values($list);
    }

    /**
     * Return a version of the list where all elements are transformed to
     * uppercase, lowercase or are left as is.
     * @param array $data
     * @param int $use_case
     * @return array
     */
    public static function fitCase(array $data, int $use_case = self::CASE_ASIS): array
    {
        return match ($use_case) {
            self::CASE_ASIS => $data,
            self::CASE_LOWER => self::toLowerCase($data),
            self::CASE_UPPER => self::toUpperCase($data),
            default => throw new LogicException("invalid value for use_case: '$use_case'"),
        };
    }

    /**
    * Return the list where all entries are transformed to lower case.
    * @param array $list
    * @return array
    */
    public static function toLowerCase(array $list): array
    {
        return array_map(function ($element) { return mb_strtolower($element); }, $list);
    }

    /**
     * Return the list where all entries are transformed to lower case.
     * @param array $list
     * @return array
     */
    public static function toUpperCase(array $list): array
    {
        return array_map(function ($element) { return mb_strtoupper($element); }, $list);
    }

}