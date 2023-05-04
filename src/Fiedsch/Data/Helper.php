<?php

/**
 * @package    data_util
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/data_util
 */
namespace Fiedsch\Data;

use RuntimeException;
use function count;
use function abs;

class Helper {

    /*
     * In surveys that run over a longer period of time, we often have names for waves created from number of the wave
     * and calendar year like '09-2023' or '2023-09'. In order to get the name for the wave x steps forward or back
     * (e.g. '09-2023' three waves back would be '06-2023') the utility function Helper::moveWave() can be used.
     * The following constants are used to specify where the wave part is expected (compare '09-2023' and '2023-09')
     * when parsing the string.
     */
    const ORDER_WAVE_FIRST = 1;
    const ORDER_WAVE_LAST = 2;

    /**
     * Split two strings and create a string containing the common parts plus the
     * distinct numerical indices that can be used to create a list of strings that
     * are from, to, and everything in between.
     *
     * The format of the result looks like the input to in bash's brace expansion
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
     * @throws RuntimeException
     */
    public static function getExpression(string $from, string $to): string
    {
        // Split inputs in parts containing digits and "not digits" (everything else).
        // While doing this map input to lower, so we don't have to care about case.

        $from_split = preg_split('/(\d+)/', $from, -1, PREG_SPLIT_DELIM_CAPTURE);
        $to_split = preg_split('/(\d+)/', $to, -1, PREG_SPLIT_DELIM_CAPTURE);

        // Check if the "not digits" parts match. If not, throw an exception
        // as we will not be able to complete the task.

        // (a) minimum requirement: they have to have the same length.
        if (count($from_split) != count($to_split)) {
            throw new RuntimeException("'$from' and '$to' do not match [length error]");
        }
        // (b) a bit more into detail: the "not digits" parts have to be equal.
        // Build the result while performing the checks

        $result = '';

        foreach ($from_split as $i => $part) {
            if (ctype_digit($part)) {
                // Do not compare as this is the distinct parts we are interested in to create the expansion.
                // If there is nothing to expand just return the value.
                if ($part === $to_split[$i]) {
                    $result .= $part;
                } else {
                    $result .= sprintf('{%s,%s}', $part, $to_split[$i]);
                }
            } else {
                if ($part !== $to_split[$i]) {
                    throw new RuntimeException("'$from' and '$to' do not match [error while comparing '$part' and '$to_split[$i]']");
                }
                $result .= $part;
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
    public static function expandExpression(string $expression): array
    {
        if ('' === $expression) {
            return [];
        }

        if (!str_contains($expression, '{')) {
            return [$expression];
        }

        if (!str_contains($expression, '}')) {
            throw new RuntimeException("did not find matching '}'");
        }

        $parts = preg_split('/\{(\d+),(\d+)\}/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return self::expandArray($parts);
    }

    /**
     * @param array $parts
     * @return array
     */
    protected static function expandArray(array $parts): array
    {
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
            array_unshift($parts, $next);
            $temp = array_merge($result, self::expandArray($parts));
            $temp = array_map(function($element) use ($curr) { return $curr.$element; }, $temp);
            $result = array_merge($result, $temp);
        }
        return $result;
    }

    /**
     * Essentially PHPs range() with numeric parameters, but we also handle leading '0's.
     *
     * @param string $start
     * @param string $stop
     * @return array
     */
    public static function makeSequence(string $start, string $stop): array
    {
        if (!ctype_digit($start) || !ctype_digit($stop)) {
            throw new RuntimeException("makeSequence only allows numeric parameters");
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
    public static function prefixWithZeros(mixed $value, int $count): string
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
     * (originally defined in https://github.com/fiedsch/datamanagement/ Fiedsch/Data/File/Helper.php,
     * but it seems to fit here better).
     *
     * @param string $name Name of the column, case-insensitive.
     *
     * @return int zero based index that corresponds to the `$name`
     */
    public static function columnIndex(string $name): int
    {
        // name consists of a single letter
        if (!preg_match("/^[A-Z]+$/i", $name)) {
            throw new RuntimeException("invalid column name '$name'");
        }
        // solve longer names recursively
        if (preg_match("/^([A-Z])([A-Z]+)$/i", $name, $matches)) {
            return pow(26, strlen($matches[2])) * (self::ColumnIndex($matches[1]) + 1) + self::ColumnIndex($matches[2]);
        }
        return ord(strtoupper($name)) - 64 - 1;
    }

    /**
     * Map number in [1,26] to letter [a,z]
     * @param int $i
     * @return string
     * @throws RuntimeException
     */
    protected static function toLetter(int $i): string
    {
        if ($i<1 || $i>26) {
            throw new RuntimeException("Invalid number '$i'. Must be in range [1,26].");
        }
        return chr(64+$i);
    }

    /**
     * Convert number >=0 to letter(s) (like Excel column names)
     *
     * @param int $i
     * @return string
     * @throws RuntimeException
     */
    public static function columnName(int $i): string
    {
        if ($i==0) { return 'A'; }

        $b = $i % 26;
        $a = intdiv($i - $b, 26);
        return sprintf("%s%s",
            $a==0 ? '' : self::columnName($a-1),
            $b==0 ? self::toLetter(1) : self::toLetter($b+1));
    }

    /**
     * Prepend entries of a column name array to a column name mapping and rebase the mapping.
     * The column mapping may contain gaps that will be mapped accordingly.
     * Example:
     * prependAndRemap(['one'=>'A', 'two'=>'C'], ['three']) === ['three'=>'A','one'=>'B', 'two'=>'D'].
     *
     * @param array $base the base mapping
     * @param array $add an array of column names
     *
     * @return array
     * @throws RuntimeException
     */
    public static function prependAndRemap(array $base, array $add): array
    {
        // test for duplicate keys
        $duplicates = [];
        foreach ($add as $k) {
            if (in_array($k, array_keys($base))) {
                $duplicates[] = $k;
            }
        }
        if ($duplicates) {
            throw new RuntimeException("can not remap because of duplicate array keys: '". json_encode($duplicates)."' found in both mappings");
        }

        $result = [];
        $idx = 0;
        foreach ($add as $k) {
            $result[$k] = self::columnName($idx++);
        }
        foreach ($base as $k => $v) {
            $result[$k] = self::columnName( self::columnIndex($v) + count($add) );
        }
        return $result;
    }

    /**
     * Consider waves in a study (a wave could e.g. be the month or the week). This function helps to move the specification
     * of a wave x waves forward or backward.
     * Example: '09-2023' back three waves would be '06-2023', back 12 waves would be '06-2022' (when we consider 12
     * waves per year, i.e. months).
     *
     * @param string $wave must be formatted so it matches the specifications in $pattern and $order
     * @param int $step move x waves forward ($step > 0) or backward ($step < 0)
     * @param string $pattern pattern for the wave and year parts (a regular expression including an optional separator like '-' in '09-2023' or '/' in '2023/09').
     *                        Use two digit years at your own risk as we did not fully consider that case: what does 64 in '09/64' mean: 1964 or 2064?
     * @param int $order specifies if the wave part comes first or last ('09-2023' or '2023-09' respectively)
     * @param int $wavesPerYear the amount of waves per year we want to consider (defaults to 12, i.e. wave == month)
     */
    public static function moveWave(string $wave, int $step, string $pattern = '(\d{2})(-)(\d{4})', int $order = self::ORDER_WAVE_FIRST, int $wavesPerYear = 12): string
    {
        if (abs($step) > $wavesPerYear) {
            throw new RuntimeException('Sorry, we currently only handle step values <= '.$wavesPerYear); // TODO we obviously want to fix that below
        }
        // Extract the lengths of the wave and year sub patterns from $pattern extrahieren (with the default pattern this would be 2 and 4):
        if (!preg_match('/{(\d)}.*{(\d)}/', $pattern, $matches)) {
            throw new RuntimeException("Invalid pattern specification: Expected to find two digits for wave's and year's respective lengths");
        }
        if (self::ORDER_WAVE_FIRST === $order) {
            $pattern_wave_length = (int)$matches[1];
            /** @noinspection PhpUnusedLocalVariableInspection */
            $pattern_year_length = (int)$matches[2];
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $pattern_year_length = (int)$matches[1];
            $pattern_wave_length = (int)$matches[2];
        }

        if (!preg_match("/$pattern/", $wave, $matches)) {
            throw new RuntimeException("Parameters \$wave and \$pattern do not match: Expected $pattern and got $wave");
        }
        // Jahr und Welle aus $wave extrahieren
        if (self::ORDER_WAVE_FIRST === $order) {
            $extracted_wave = (int)$matches[1];
            $extracted_separator = $matches[2];
            $extracted_year = (int)$matches[3];
        } else {
            $extracted_year = (int)$matches[1];
            $extracted_separator = $matches[2];
            $extracted_wave = (int)$matches[3];
        }

        // Fix $wave_ and $year when switching to a different year
        $moved_wave = $extracted_wave + $step;
        $moved_year = $extracted_year;

        if ($moved_wave < 1) {
            $moved_year -= 1;
            $moved_wave = $moved_wave + $wavesPerYear;
        }
        if ($moved_wave > $wavesPerYear) {
            $moved_year += 1;
            $moved_wave = $moved_wave - $wavesPerYear;
        }

        if (self::ORDER_WAVE_FIRST === $order) {
            return sprintf('%s%s%s',
                str_pad($moved_wave, $pattern_wave_length, '0', STR_PAD_LEFT),
                $extracted_separator,
                $moved_year
            );
        } else {
            return sprintf('%s%s%s',
                $moved_year,
                $extracted_separator,
                str_pad($moved_wave, $pattern_wave_length, '0', STR_PAD_LEFT)
            );
        }
    }

}
