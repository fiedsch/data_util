<?php

/**
 * @package    data_util
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/data_util
 */
namespace Fiedsch\Data;

class Helper {

    /**
     * Split two strings and create a string containing the common parts plus the
     * distinct numerical indices that can be used to create a list of strings that
     * are from, to, and everything in between.
     *
     * The format of the result looks like the input to in bashs brace expansion
     * (https://www.gnu.org/software/bash/manual/html_node/Brace-Expansion.html)
     * but has a different "meaning"!
     *
     * Example:
     * <pre>
     * getExpression('a_1', 'a_5') => 'a_{1,5}'
     * </pre>
     * which could be expanded to 'a_1 a_2 a_3 a_4 a_5' using expandExpression()
     * whereas bash would expand to 'a_1 a_5'.
     *
     * @param string $from
     * @param string $to
     * @return string
     * @throws \RuntimeException
     */
    public static function getExpression($from, $to)
    {
        // Split inputs in parts containing digits and "not digits" (everything else).
        // While doing this map input to lower so we don't have to care about case.

        $from_split = preg_split('/(\d+)/', $from, null, PREG_SPLIT_DELIM_CAPTURE);
        $to_split = preg_split('/(\d+)/', $to, null, PREG_SPLIT_DELIM_CAPTURE);

        // Check if the "not digits" parts match. If not, throw an exception
        // as we will not be able to complete the task.

        // (a) minimum requirement: they have to have the same length.
        if (count($from_split) != count($to_split)) {
            throw new \RuntimeException("'$from' and '$to' do not match [length error]");
        }
        // (b) a bit more into detail: the "not digits" parts have to be equal.
        // Build the result while performing the checks

        $result = '';

        foreach ($from_split as $i => $part) {
            if (ctype_digit($part)) {
                // Do not compare as this is the distinct parts we are interested in to create the expansion.
                // If there is nothing to expand just return the value.
                if ($from_split[$i] === $to_split[$i]) {
                    $result .= $from_split[$i];
                } else {
                    $result .= sprintf('{%s,%s}', $from_split[$i], $to_split[$i]);
                }
            } else {
                if ($from_split[$i] !== $to_split[$i]) {
                    throw new \RuntimeException("'$from' and '$to' do not match [error while comparing '$from_split[$i]' and '$to_split[$i]']");
                }
                $result .= $from_split[$i];
            }
        }
        return $result;
    }


    /**
     * Split a string created by getExpression() and create an array of strings. If the
     * input contains more than one {[digits],[digits]} part they will be expanded from
     * right to left.
     *
     * Example:
     * <pre>
     * expandExpression('a{1,3}_{1,2}' yields [ 'a1_1', 'a1_2', 'a2_1', 'a2_2', 'a3_1', 'a3_2' ]
     * </pre>
     *
     * Hint:
     * you might alternatively use bash and let it expand a{1,2,3}_{1,2} which is sometimes more
     * powerful as it could also expand {x,y,z}_{1,2,3}_{a,b}_{1,2}.
     *
     * Question:
     * Is the fact that 'a_{001,004}' yields the same result as 'a_{001,4}' a bug or a feature?
     *
     * @param string $expression
     * @return array
     */
    public static function expandExpression($expression)
    {
        if ('' === $expression) {
            return [];
        }

        if (strpos($expression, '{') === false) {
            return [$expression];
        }

        if (strpos($expression, '}') === false) {
            throw new \RuntimeException("did not find matching '}'");
        }

        $parts = preg_split('/\{(\d+),(\d+)\}/', $expression, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return self::expandArray($parts);
    }

    /**
     * @param array $parts
     * @return array
     */
    protected static function expandArray($parts)
    {
        if (!is_array($parts)) {
            throw new \RuntimeException("expected an array");
        }

        if (count($parts) == 1) {
            return $parts;
        }

        $curr = array_shift($parts);
        $next = array_shift($parts);

        if (null === $next) {
            return [ $curr ];
        }

        $result = [ ];

        if (ctype_digit($curr) && ctype_digit($next)) {
            foreach (self::makeSequence($curr, $next) as $prefix) {
                $temp = self::expandArray($parts);
                $temp = array_map(function($element) use ($prefix) { return $prefix.$element; }, $temp);
                $result = array_merge($result, $temp);
            }
        } else {
            if (null !== $next) {
                array_unshift($parts, $next);
            }
            $temp = array_merge($result, self::expandArray($parts));
            $temp = array_map(function($element) use ($curr) { return $curr.$element; }, $temp);
            $result = array_merge($result, $temp);
        }
        return $result;
    }

    /**
     * Essentially PHPs range() with numeric parameters but we also handle leading '0's.
     *
     * @param int $start
     * @param int $stop
     * @return array
     */
    public static function makeSequence($start, $stop)
    {
        if (!ctype_digit($start) || !ctype_digit($stop)) {
            throw new \RuntimeException("makeSequence only allows numeric parameters");
        }

        $result = range($start, $stop);

        // handle leading '0's
        if (preg_match("/^(0+)/", $start, $matches)){
            $prefix = $matches[1];
            $num_zeros = strlen($prefix);
            $result = array_map(function($element) use ($num_zeros) { return self::prefixWithZeros($element, $num_zeros); }, $result);
        }
        return $result;
    }

    /**
     * Prefix a value with zeros so that for example '7' becomes '007'
     *
     * This function is not to be confused with PHPs str_pad() which would
     * *pad* the string with zeros. We want to *prefix* with a given numbers
     * of zeros.
     *
     * @param mixed $value
     * @param int $count
     * @return string
     */
    public static function prefixWithZeros($value, $count)
    {
        $prefix_count = $count - strlen($value) + 1;
        if ($prefix_count > 0) {
            $value = str_repeat('0', $prefix_count).$value;
        }
        return $value;
    }

    /**
     * Get the zero based index corresponding to the spreadsheet column (A, B, ..., Z, AA, AB, ...).
     *
     * (originally defined in https://github.com/fiedsch/datamanagement/ Fiedsch/Data/File/Helper.php
     * but it seems to fit here better).
     *
     * @param string $name Name of the column, case insensitive.
     *
     * @return int|number zero based index that corresponds to the `$name`
     */
    public static function columnIndex($name)
    {
        // name consists of a single letter
        if (!preg_match("/^[A-Z]+$/i", $name)) {
            throw new \RuntimeException("invalid column name '$name'");
        }
        // solve longer names recursively
        if (preg_match("/^([A-Z])([A-Z]+)$/i", $name, $matches)) {
            return pow(26, strlen($matches[2])) * (self::ColumnIndex($matches[1]) + 1) + self::ColumnIndex($matches[2]);
        }
        return ord(strtoupper($name)) - 64 - 1;
    }

    /**
     * Map number in [1,26] to letter [a,z]
     * @param integer $i
     * @return string
     * @throws \RuntimeException
     */
    protected static function toLetter($i)
    {
        if ($i<1 || $i>26) {
            throw new \RuntimeException("Invalid number '$i'. Must be in range [1,26].");
        }
        return chr(64+$i);
    }

    /**
     * Convert number >=0 to letter(s) (like Excel column names)
     *
     * @param integer $i
     * @return string
     * @throws \RuntimeException
     */
    public static function columnName($i)
    {
        if ($i==0) { return 'A'; }

        $b = $i % 26;
        $a = intdiv($i - $b, 26);
        return sprintf("%s%s",
            $a==0 ? '' : self::columnName($a-1),
            $b==0 ? self::toLetter(1) : self::toLetter($b+1));
    }

}