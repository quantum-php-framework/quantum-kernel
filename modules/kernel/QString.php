<?php

namespace  Quantum;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * Class QString
 * @package Quantum
 */
class QString implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var QString
     */
    public $text;
    /**
     * @var bool|QString
     */
    public $encoding;

    /**
     * @var array
     */
    protected static $camelCache = [];
    /**
     * @var array
     */
    protected static $studlyCache = [];
    /**
     * @var array
     */
    protected static $snakeCache = [];


    /**
     * QString constructor.
     * @param QString $text
     */
    function __construct($text = '', $encoding = null)
    {
        if (is_array($text))
        {
            throw new InvalidArgumentException(
                'Passed value cannot be an array'
            );
        }
        elseif (is_object($text) && !method_exists($text, '__toString'))
        {
            throw new InvalidArgumentException(
                'Passed object must have a __toString method'
            );
        }

        $this->text = (string)$text;
        $this->encoding = $encoding ?: \mb_internal_encoding();
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->text;
    }

    /**
     * @return bool|QString|QString
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @return QString|QString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns true if the string contains only alphabetic chars, false
     * otherwise.
     *
     * @return bool Whether or not $str contains only alphabetic chars
     */
    public function isAlpha()
    {
        return $this->matchesPattern('^[[:alpha:]]*$');
    }

    /**
     * Returns true if the string contains a number
     * otherwise.
     *
     * @return bool Whether or not $str contains only alphabetic chars
     */
    public function isNumber()
    {
        return is_numeric($this->text);
    }

    /**
     * Returns true if the string contains a number
     * otherwise.
     *
     * @return bool Whether or not $str contains only alphabetic chars
     */
    public function isInteger()
    {
        return is_integer($this->text);
    }

    /**
     * Returns true if the string contains a number
     * otherwise.
     *
     * @return bool Whether or not $str contains only alphabetic chars
     */
    public function isDecimal()
    {
        return is_double($this->text);
    }

    /**
     * Returns true if the string contains a single precision floating number
     * otherwise.
     *
     * @return bool s
     */
    public function isFloat()
    {
        return is_float($this->text);
    }

    /**
     * Returns true if the string contains a double precision number
     * otherwise.
     *
     * @return bool s
     */
    public function isDouble()
    {
        return is_double($this->text);
    }

    /**
     * Returns true if the string contains a number
     * otherwise.
     *
     * @return bool Whether or not $str contains only alphabetic chars
     */
    public function isNumeric()
    {
        return is_numeric($this->text);
    }



    /**
     * Returns true if the string contains only alphabetic and numeric chars,
     * false otherwise.
     *
     * @return bool Whether or not $str contains only alphanumeric chars
     */
    public function isAlphanumeric()
    {
        return $this->matchesPattern('^[[:alnum:]]*$');
    }

    /**
     * @return bool
     */
    public function isAlphaNumericWithSpaces()
    {
        if (preg_match('/[^\x00-\x7F]/', $this->text)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isAlphaNumericWithSpaceAndDash()
    {
        if (preg_match('/^[\w\-\s]+$/', $this->text)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the string contains only hexadecimal chars, false
     * otherwise.
     *
     * @return bool Whether or not $str contains only hexadecimal chars
     */
    public function isHexadecimal()
    {
        return $this->matchesPattern('^[[:xdigit:]]*$');
    }

    /**
     * Returns true if the string is JSON, false otherwise. Unlike json_decode
     * in PHP 5.x, this method is consistent with PHP 7 and other JSON parsers,
     * in that an empty string is not considered valid JSON.
     *
     * @return bool Whether or not $str is JSON
     */
    public function isJson()
    {
        if (!$this->length())
            return false;

        json_decode($this->text);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Returns true if the string is Html, false otherwise
     *
     * @return bool Whether or not $str is Html
     */

    public function isHtml()
    {
        if (!$this->length())
            return false;

        return $this->text != strip_tags($this->text);
    }

    /**
     * Returns true if the string is XMl, false otherwise
     *
     * @return bool Whether or not $str is XML
     */
    public function isXml()
    {
        if (!$this->length())
            return false;

        $doc = @simplexml_load_string($this->text);
        if ($doc) {
            return true; //this is valid
        } else {
            return false; //this is not valid
        }
    }


    /**
     * @return bool
     */
    public function isMultiByte()
    {
        return preg_match('/[^\x00-\x7F]/', $this->text);
    }

    /**
     * Returns true if the string contains only lower case chars, false
     * otherwise.
     *
     * @return bool Whether or not $str contains only lower case characters
     */
    public function isLowerCase()
    {
        return $this->matchesPattern('^[[:lower:]]*$');
    }

    /**
     * Returns true if the string is base64 encoded, false otherwise.
     *
     * @return bool Whether or not $str is base64 encoded
     */
    public function isBase64()
    {
        return (base64_encode(base64_decode($this->text, true)) === $this->text);
    }

    /**
     * Returns true if the string is serialized, false otherwise.
     *
     * @return bool Whether or not $str is serialized
     */
    public function isSerialized()
    {
        return $this->text === 'b:0;' || @unserialize($this->text) !== false;
    }

    /**
     * Returns true if the string contains only lower case chars, false
     * otherwise.
     *
     * @return bool Whether or not $str contains only lower case characters
     */
    public function isUpperCase()
    {
        return $this->matchesPattern('^[[:upper:]]*$');
    }

    /**
     * Returns true if the string contains only lower case chars, false
     * otherwise.
     *
     * @return bool Whether or not $str contains only lower case characters
     */
    public function isDate()
    {
        if (strtotime($this->text) !== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEmail()
    {
        if (filter_var($this->text, FILTER_VALIDATE_EMAIL) !== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isUrl()
    {
        return !filter_var($this->text, FILTER_VALIDATE_URL) === false;
    }

    /**
     * @return bool
     */
    public function isIp()
    {
        return filter_var($this->text, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @return bool
     */
    public function isMacAddress()
    {
        return filter_var($this->text, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * @return bool
     */
    public function isRegex()
    {
        return filter_var($this->text, FILTER_VALIDATE_REGEXP) !== false;
    }

    /**
     * @return bool
     */
    public function isDomain()
    {
        return filter_var($this->text, FILTER_VALIDATE_DOMAIN) !== false;
    }

    /**
     * @return bool
     */
    public function isIpV6()
    {
        return (filter_var($this->text, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false);
    }

    /**
     * @return bool
     */
    public function isIpV4()
    {
        return (filter_var($this->text, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

    /**
     * @return bool
     */
    public function isUuid()
    {
        $uuid = $this->text;

        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        }
        return true;
    }

    /**
     * @return false|int
     */
    public function isRgbColor()
    {
        return preg_match('/^rgb\\(\\s*(0|[1-9]\\d?|1\\d\\d?|2[0-4]\\d|25[0-5])\\s*,\\s*(0|[1-9]\\d?|1\\d\\d?|2[0-4]\\d|25[0-5])\\s*,\\s*(0|[1-9]\\d?|1\\d\\d?|2[0-4]\\d|25[0-5])\\s*\\)$/', $this->text);
    }

    /**
     * Returns true if the string contains a lower case char, false
     * otherwise.
     *
     * @return bool Whether or not the string contains a lower case character.
     */
    public function hasLowerCase()
    {
        return $this->matchesPattern('.*[[:lower:]]');
    }
    /**
     * Returns true if the string contains an upper case char, false
     * otherwise.
     *
     * @return bool Whether or not the string contains an upper case character.
     */
    public function hasUpperCase()
    {
        return $this->matchesPattern('.*[[:upper:]]');
    }

    /**
     * Splits on newlines and carriage returns, returning an array of QString
     * objects corresponding to the lines in the string.
     *
     * @return static[] An array of QString objects
     */
    public function lines()
    {
        $array = $this->split('[\r\n]{1,2}', $this->text);
        for ($i = 0; $i < count($array); $i++) {
            $array[$i] = static::create($array[$i], $this->encoding);
        }
        return $array;
    }

    /**
     * Ensures that the string ends with $substring. If it doesn't, it's
     * appended.
     *
     * @param  QString $substring The substring to add if not present
     * @return static Object with its $str suffixed by the $substring
     */
    public function ensureRight($substring)
    {
        $string = static::create($this->text, $this->encoding);
        if (!$string->endsWith($substring)) {
            $string->text .= $substring;
        }
        return $string;
    }

    /**
     * Ensures that the string begins with $substring. If it doesn't, it's
     * prepended.
     *
     * @param  QString $substring The substring to add if not present
     * @return static Object with its $str prefixed by the $substring
     */
    public function ensureLeft($substring)
    {
        $string = static::create($this->text, $this->encoding);
        if (!$string->startsWith($substring)) {
            $string->text = $substring . $string->text;
        }
        return $string;
    }

    /**
     * Returns the number of occurrences of $substring in the given string.
     * By default, the comparison is case-sensitive, but can be made insensitive
     * by setting $caseSensitive to false.
     *
     * @param  QString $substring     The substring to search for
     * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
     * @return int    The number of $substring occurrences
     */
    public function countSubstr($substring, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return \mb_substr_count($this->text, $substring, $this->encoding);
        }
        $str = \mb_strtoupper($this->text, $this->encoding);
        $substring = \mb_strtoupper($substring, $this->encoding);
        return \mb_substr_count($str, $substring, $this->encoding);
    }

    /**
     * Returns a lowercase and trimmed string separated by dashes. Dashes are
     * inserted before uppercase characters (with the exception of the first
     * character of the string), and in place of spaces as well as underscores.
     *
     * @return static Object with a dasherized $str
     */
    public function dasherize()
    {
        return $this->delimit('-');
    }

    /**
     * Splits the string with the provided regular expression, returning an
     * array of QString objects. An optional integer $limit will truncate the
     * results.
     *
     * @param  QString   $pattern The regex with which to split the string
     * @param  int      $limit   Optional maximum number of results to return
     * @return static[] An array of QString objects
     */
    public function split($pattern, $limit = null)
    {
        if ($limit === 0) {
            return [];
        }
        // mb_split errors when supplied an empty pattern in < PHP 5.4.13
        // and HHVM < 3.8
        if ($pattern === '') {
            return [static::create($this->text, $this->encoding)];
        }
        $regexEncoding = $this->regexEncoding();
        $this->regexEncoding($this->encoding);
        // mb_split returns the remaining unsplit string in the last index when
        // supplying a limit
        $limit = ($limit > 0) ? $limit += 1 : -1;
        static $functionExists;
        if ($functionExists === null) {
            $functionExists = function_exists('\mb_split');
        }
        if ($functionExists) {
            $array = \mb_split($pattern, $this->text, $limit);
        } else if ($this->supportsEncoding()) {
            $array = \preg_split("/$pattern/", $this->text, $limit);
        }
        $this->regexEncoding($regexEncoding);
        if ($limit > 0 && count($array) === $limit) {
            array_pop($array);
        }
        for ($i = 0; $i < count($array); $i++) {
            $array[$i] = static::create($array[$i], $this->encoding);
        }
        return $array;
    }

    /**
     * @return array|
     */
    function toArray()
    {
        return str_split($this->text);
    }

    /**
     * Returns a lowercase and trimmed string separated by underscores.
     * Underscores are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces as well as
     * dashes.
     *
     * @return static Object with an underscored $str
     */
    public function underscored()
    {
        return $this->delimit('_');
    }

    /**
     * Converts the first character of the supplied string to upper case.
     *
     * @return static Object with the first character of $str being upper case
     */
    public function upperCaseFirst()
    {
        $first = \mb_substr($this->text, 0, 1, $this->encoding);
        $rest = \mb_substr($this->text, 1, $this->length() - 1,
            $this->encoding);
        $str = \mb_strtoupper($first, $this->encoding) . $rest;
        return static::create($str, $this->encoding);
    }


    /**
     * Converts the first character of the string to lower case.
     *
     * @return static Object with the first character of $str being lower case
     */
    public function lowerCaseFirst()
    {
        $first = \mb_substr($this->text, 0, 1, $this->encoding);
        $rest = \mb_substr($this->text, 1, $this->length() - 1,
            $this->encoding);
        $str = \mb_strtolower($first, $this->encoding) . $rest;
        return static::create($str, $this->encoding);
    }


    /**
     * Returns the longest common substring between the string and $otherStr.
     * In the case of ties, it returns that which occurs first.
     *
     * @param  QString $otherStr Second string for comparison
     * @return static Object with its $str being the longest common substring
     */
    public function longestCommonSubstring($otherStr)
    {
        // Uses dynamic programming to solve
        // http://en.wikipedia.org/wiki/Longest_common_substring_problem
        $encoding = $this->encoding;
        $string = static::create($this->text, $encoding);
        $strLength = $string->length();
        $otherLength = \mb_strlen($otherStr, $encoding);
        // Return if either string is empty
        if ($strLength == 0 || $otherLength == 0) {
            $string->text = '';
            return $string;
        }
        $len = 0;
        $end = 0;
        $table = array_fill(0, $strLength + 1,
            array_fill(0, $otherLength + 1, 0));
        for ($i = 1; $i <= $strLength; $i++) {
            for ($j = 1; $j <= $otherLength; $j++) {
                $strChar = \mb_substr($string->text, $i - 1, 1, $encoding);
                $otherChar = \mb_substr($otherStr, $j - 1, 1, $encoding);
                if ($strChar == $otherChar) {
                    $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
                    if ($table[$i][$j] > $len) {
                        $len = $table[$i][$j];
                        $end = $i;
                    }
                } else {
                    $table[$i][$j] = 0;
                }
            }
        }
        $string->text = \mb_substr($string->text, $end - $len, $len, $encoding);
        return $string;
    }

    /**
     * Returns the longest common prefix between the string and $otherStr.
     *
     * @param  QString $otherStr Second string for comparison
     * @return static Object with its $str being the longest common prefix
     */
    public function longestCommonPrefix($otherStr)
    {
        $encoding = $this->encoding;
        $maxLength = min($this->length(), \mb_strlen($otherStr, $encoding));
        $longestCommonPrefix = '';
        for ($i = 0; $i < $maxLength; $i++) {
            $char = \mb_substr($this->text, $i, 1, $encoding);
            if ($char == \mb_substr($otherStr, $i, 1, $encoding)) {
                $longestCommonPrefix .= $char;
            } else {
                break;
            }
        }
        return static::create($longestCommonPrefix, $encoding);
    }

    /**
     * Returns a trimmed string with the first letter of each word capitalized.
     * Also accepts an array, $ignore, allowing you to list words not to be
     * capitalized.
     *
     * @param  array  $ignore An array of words not to capitalize
     * @return static Object with a titleized $str
     */
    public function titleize($ignore = null)
    {
        $string = static::create($this->trim(), $this->encoding);
        $encoding = $this->encoding;
        $string->text = preg_replace_callback(
            '/([\S]+)/u',
            function ($match) use ($encoding, $ignore) {
                if ($ignore && in_array($match[0], $ignore)) {
                    return $match[0];
                }
                $string = new QString($match[0], $encoding);
                return (string) $string->toLowerCase()->upperCaseFirst();
            },
            $string->text
        );
        return $string;
    }

    /**
     * Capitalizes the first word of the string, replaces underscores with
     * spaces, and strips '_id'.
     *
     * @return static Object with a humanized $str
     */
    public function humanize()
    {
        $str = str_replace(['_id', '_'], ['', ' '], $this->text);
        return static::create($str, $this->encoding)->trim()->upperCaseFirst();
    }

    /**
     * Returns a string with smart quotes, ellipsis characters, and dashes from
     * Windows-1252 (commonly used in Word documents) replaced by their ASCII
     * equivalents.
     *
     * @return static Object whose $str has those characters removed
     */
    public function tidy()
    {
        $str = preg_replace([
            '/\x{2026}/u',
            '/[\x{201C}\x{201D}]/u',
            '/[\x{2018}\x{2019}]/u',
            '/[\x{2013}\x{2014}]/u',
        ], [
            '...',
            '"',
            "'",
            '-',
        ], $this->text);
        return static::create($str, $this->encoding);
    }

    /**
     * Inserts $substring into the string at the $index provided.
     *
     * @param  QString $substring QString to be inserted
     * @param  int    $index     The index at which to insert the substring
     * @return static Object with the resulting $str after the insertion
     */
    public function insert($substring, $index)
    {
        $string = static::create($this->text, $this->encoding);
        if ($index > $string->length()) {
            return $string;
        }
        $start = \mb_substr($string->text, 0, $index, $string->encoding);
        $end = \mb_substr($string->text, $index, $string->length(),
            $string->encoding);
        $string->text = $start . $substring . $end;
        return static::create($string);
    }

    /**
     * @param $substring
     * @param $needle
     * @return QString
     */
    public function insertBeforeLastOcurrenceOf($substring, $needle)
    {
        $index = $this->lastIndexOf($needle);

        return $this->insert($substring, $index);
    }

    /**
     * @param $substring
     * @param $needle
     * @return QString
     */
    public function insertBeforeFirstOcurrenceOf($substring, $needle)
    {
        $index = $this->IndexOf($needle);

        return $this->insert($substring, $index);
    }

    /**
     * @param $substring
     * @param $needle
     * @return QString
     */
    public function insertAfterLastOcurrenceOf($substring, $needle)
    {
        $index = $this->lastIndexOf($needle);

        return $this->insert($substring, $index+1);
    }

    /**
     * @param $substring
     * @param $needle
     * @return QString
     */
    public function insertAfterFirstOcurrenceOf($substring, $needle)
    {
        $index = $this->indexOf($needle);

        return $this->insert($substring, $index + strlen($needle));
    }

    /**
     * Surrounds $str with the given substring.
     *
     * @param  QString $substring The substring to add to both sides
     * @return static Object whose $str had the substring both prepended and
     *                 appended
     */
    public function surround($substring)
    {
        $str = implode('', [$substring, $this->text, $substring]);
        return static::create($str, $this->encoding);
    }

    /**
     * Returns a case swapped version of the string.
     *
     * @return static Object whose $str has each character's case swapped
     */
    public function swapCase()
    {
        $string = static::create($this->text, $this->encoding);
        $encoding = $string->encoding;
        $string->text = preg_replace_callback(
            '/[\S]/u',
            function ($match) use ($encoding) {
                if ($match[0] == \mb_strtoupper($match[0], $encoding)) {
                    return \mb_strtolower($match[0], $encoding);
                }
                return \mb_strtoupper($match[0], $encoding);
            },
            $string->text
        );
        return $string;
    }

    /**
     * Returns a boolean representation of the given logical string value.
     * For example, 'true', '1', 'on' and 'yes' will return true. 'false', '0',
     * 'off', and 'no' will return false. In all instances, case is ignored.
     * For other numeric strings, their sign will determine the return value.
     * In addition, blank strings consisting of only whitespace will return
     * false. For all other strings, the return value is a result of a
     * boolean cast.
     *
     * @return bool A boolean value for the string
     */
    public function toBoolean()
    {
        $key = $this->toLowerCase()->str;
        $map = [
            'true'  => true,
            '1'     => true,
            'on'    => true,
            'yes'   => true,
            'false' => false,
            '0'     => false,
            'off'   => false,
            'no'    => false
        ];
        if (array_key_exists($key, $map)) {
            return $map[$key];
        } elseif (is_numeric($this->text)) {
            return (intval($this->text) > 0);
        }
        return (bool) $this->regexReplace('[[:space:]]', '')->text;
    }

    /**
     * @return QString
     */
    function crc32()
    {
        return static::create(crc32($this->text));
    }

    /**
     * @return QString
     */
    function crc32b()
    {
        return static::create($this->hash('crc32b'));
    }

    /**
     * @return QString
     */
    function md5()
    {
        return static::create(md5($this->text));
    }

    /**
     * @param QString $algo
     * @return QString
     */
    function hash($algo = 'sha256')
    {
        return static::create(hash($algo, $this->text));
    }

    /**
     * Determine the hashcode of a string,
     * algorithm matches the hashCode method available in a Java QString class
     * @return QString
     */
    public function hashCode()
    {
        $s = $this->text;
        $h = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i ++) {
            $h = $this->overflow32(31 * $h + ord($s[$i]));
        }
        return static::create($h);
    }

    /**
     * @param QString $algo
     * @return QString
     */
    function sha1()
    {
        return static::create(sha1($this->text));
    }

    /**
     * @param QString $salt
     * @return QString
     */
    function crypt($salt = null)
    {
        if ($salt)
            return static::create(crypt($this->text, $salt));

        return static::create(password_hash($this->text, PASSWORD_BCRYPT));
    }

    /**
     * @return int
     */
    function length()
    {
        return mb_strlen($this->text);
    }

    /**
     * @return int
     */

    function width()
    {
        return mb_strwidth($this->text);
    }

    /**
     * Returns the length of the string, implementing the countable interface.
     *
     * @return int The number of characters in the string, given the encoding
     */
    public function count()
    {
        return $this->length();
    }

    /**
     * @param $string
     */
    function append($string)
    {
        return static::create($this->text.$string);
    }


    /**
     * @param $string
     */
    function prepend($string)
    {
        return static::create($string.$this->text);
    }

    /**
     * @return bool
     */
    function isEmpty()
    {
        return empty($this->text);
    }

    /**
     * @return bool
     */
    function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     *
     */
    function clear()
    {
        $this->text = '';
    }

    /**
     * @param $string
     * @return bool
     */
    function equals($string)
    {
        return (strcmp($this->text, (string)$string) === 0);
    }

    /**
     * @param $string
     * @return bool
     */
    function notEquals($string)
    {
        return !$this->equals($string);
    }

    /**
     * @param $string
     * @return bool
     */
    function equalsIgnoreCase($string)
    {
        return (strcasecmp($this->text, (string)$string) === 0);
    }

    /**
     * @param $string
     * @return int
     */
    function compare($string)
    {
        return strcmp($this->text, (string)$string);
    }

    /**
     * @param $string
     * @return int
     */
    function compareIgnoreCase($string)
    {
        return strcasecmp($this->text, (string)$string);
    }

    /**
     * @param $string
     * @return bool
     */
    function slowCompare($string)
    {
        return hash_equals($this->text, (string)$string);
    }

    /**
     * @param $string
     * @return int
     */
    function compareNaturalIgnoreCase($string)
    {
        return strnatcasecmp($this->text, (string)$string);
    }

    /**
     * @param $string
     * @return int
     */
    function compareNatural($string)
    {
        return strnatcmp($this->text, (string)$string);
    }

    /**
     * @param $needles
     * @return bool
     */
    function startsWith($needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($this->text, $needle) === 0)
                return true;
        }

        return false;
    }

    /**
     * @param $needles
     * @return bool
     */
    function startsWithIgnoreCase($needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && stripos($this->text, $needle) === 0)
                return true;
        }

        return false;
    }

    /**
     * @param $needles
     * @return bool
     */
    function endsWith($needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($this->text, -strlen($needle)))
                return true;
        }

        return false;
    }

    /**
     * @param $needles
     * @return bool
     */
    function endsWithIgnoreCase($needles)
    {
        foreach ((array)$needles as $needle) {
            if (strtolower((string)$needle) === strtolower(substr($this->text, -strlen($needle))))
                return true;
        }

        return false;
    }

    /**
     * Trims the string and replaces consecutive whitespace characters with a
     * single space. This includes tabs and newline characters, as well as
     * multibyte whitespace such as the thin space and ideographic space.
     *
     * @return static Object with a trimmed $str and condensed whitespace
     */
    public function collapseWhitespace()
    {
        return $this->regexReplace('[[:space:]]+', ' ')->trim();
    }

    /**
     * Strip all whitespace characters. This includes tabs and newline
     * characters, as well as multibyte whitespace such as the thin space
     * and ideographic space.
     *
     * @return static Object with whitespace stripped
     */
    public function stripWhitespace()
    {
        return $this->regexReplace('[[:space:]]+', '');
    }

    /**
     * Truncates the string to a given length, while ensuring that it does not
     * split words. If $substring is provided, and truncating occurs, the
     * string is further truncated so that the substring may be appended without
     * exceeding the desired length.
     *
     * @param  int    $length    Desired length of the truncated string
     * @param  QString $substring The substring to append if it can fit
     * @return static Object with the resulting $str after truncating
     */
    public function safeTruncate($length, $substring = '')
    {
        $string = static::create($this->text, $this->encoding);
        if ($length >= $string->length()) {
            return $string;
        }
        // Need to further trim the string so we can append the substring
        $encoding = $string->encoding;
        $substringLength = \mb_strlen($substring, $encoding);
        $length = $length - $substringLength;
        $truncated = \mb_substr($string->text, 0, $length, $encoding);
        // If the last word was truncated
        if (mb_strpos($string->text, ' ', $length - 1, $encoding) != $length) {
            // Find pos of the last occurrence of a space, get up to that
            $lastPos = \mb_strrpos($truncated, ' ', 0, $encoding);
            if ($lastPos !== false) {
                $truncated = \mb_substr($truncated, 0, $lastPos, $encoding);
            }
        }
        $string->text = $truncated . $substring;
        return $string;
    }

    /**
     * Returns the substring beginning at $start, and up to, but not including
     * the index specified by $end. If $end is omitted, the function extracts
     * the remaining string. If $end is negative, it is computed from the end
     * of the string.
     *
     * @param  int    $start Initial index from which to begin extraction
     * @param  int    $end   Optional index at which to end extraction
     * @return static Object with its $str being the extracted substring
     */
    public function slice($start, $end = null)
    {
        if ($end === null) {
            $length = $this->length();
        } elseif ($end >= 0 && $end <= $start) {
            return static::create('', $this->encoding);
        } elseif ($end < 0) {
            $length = $this->length() + $end - $start;
        } else {
            $length = $end - $start;
        }
        return $this->substr($start, $length);
    }

        /**
     * @param $needles
     * @return bool
     */
    function contains($needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($this->text, $needle) !== false)
                return true;
        }

        return false;
    }

    /**
     * @param $needles
     * @return bool
     */
    function containsIgnoreCase($needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && stripos($this->text, $needle) !== false)
                return true;
        }

        return false;
    }

    /**
     * @param $word
     * @return bool
     */
    function containsWholeWord($word)
    {
        return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#', $this->text);
    }

    /**
     * @param $word
     * @return bool
     */
    function containsWholeWordIgnoreCase($word)
    {
        return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $this->text);
    }

    /**
     * @param $word
     * @return int
     */
    function indexOfWholeWord($word)
    {
        $regex = '/\b' . $word . '\b/';
        preg_match($regex, $this->text, $match, PREG_OFFSET_CAPTURE);

        if (!empty($match)) {
            $pos = $match[0][1];
            return $pos;
        }

        return -1;
    }

    /**
     * @param $word
     * @return int
     */
    function indexOfWholeWordIgnoreCase($word)
    {
        $regex = '/\b' . $word . '\b/i';
        preg_match($regex, $this->text, $match, PREG_OFFSET_CAPTURE);

        if (!empty($match)) {
            $pos = $match[0][1];
            return $pos;
        }

        return -1;
    }

    /**
     * @param $needles
     * @return bool
     */
    function containsAnyOf($needles)
    {
        return $this->containsIgnoreCase($needles);
    }

    /**
     * @param $needles
     * @return bool
     */
    function containsOnly($needles)
    {
        if (empty($needles))
            return true;

        $containsAll = false;

        foreach ((array)$needles as $needle) {
            if ($needle != '' && stripos($this->text, $needle) !== false)
                $containsAll = true;
            else
                $containsAll = false;
        }

        return $containsAll;
    }

    /**
     * @return bool
     */
    function containsNonWhitespaceChars()
    {
        $chars = $this->toArray();

        foreach ($chars as $char) {
            if ($char != ' ')
                return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    function containsWhitespaceChars()
    {
        $chars = $this->toArray();

        foreach ($chars as $char) {
            if ($char == ' ')
                return true;
        }

        return false;
    }

    /**
     * @param $needles
     * @return bool|int
     */
    function indexOf($needles)
    {
        if (empty($needles))
            return 0;

        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                $result = strpos($this->text, $needle);
                if ($result > -1)
                    return $result;
            }
        }

        return -1;
    }

    /**
     * @param $needles
     * @return int
     */
    function indexOfIgnoreCase($needles)
    {
        if (empty($needles))
            return 0;

        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                $result = stripos($this->text, $needle);
                if ($result > -1)
                    return $result;
            }
        }

        return -1;
    }

    /**
     * @param $needles
     * @return bool|int
     */
    function lastIndexOf($needles)
    {
        if (empty($needles))
            return -1;

        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                $result = strrpos($this->text, $needle);
                if ($result > -1)
                    return $result;
            }
        }

        return -1;
    }

    /**
     * @param $needles
     * @return int
     */
    function lastIndexOfIgnoreCase($needles)
    {
        if (empty($needles))
            return -1;

        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                $result = strripos($this->text, $needle);
                if ($result > -1)
                    return $result;
            }
        }

        return -1;
    }

    /**
     * @return QString
     */
    function getLastCharacter()
    {
        return static::create(mb_substr($this->text, -1));
    }

    /**
     * @return QString
     */
    function getFirstCharacter()
    {
        return static::create(mb_substr($this->text, 0, 1));
    }

    /**
     * @param $start
     * @param $end
     * @return QString
     */
    function substring($start, $end)
    {
        return static::create(mb_substr($this->text, $start, $end));
    }

    /**
     * @param $numberToDrop
     * @return QString
     */
    function dropLastCharacters($numberToDrop)
    {
        $length = max(0, $this->length() - $numberToDrop);
        $txt = $this->substring(0, $length);

        return String($txt);
    }

    /**
     * @param $numberToDrop
     * @return QString
     */
    function dropFirstCharacters($numberToDrop)
    {
        $txt = $this->substring($numberToDrop, $this->length());

        return String($txt);
    }

    /**
     * @param $numCharacters
     * @return QString
     */
    function getLastCharacters($numCharacters)
    {
        $length = max(0, $this->length() - $numCharacters);
        $txt = $this->substring($length, $this->length());

        return String($txt);
    }

    /**
     * @param $numCharacters
     * @return QString
     */
    function getFirstCharacters($numCharacters)
    {
        $txt = $this->substring(0, $numCharacters);

        return String($txt);
    }

    /**
     * @param $string
     * @param bool $includeSubStringInResult
     * @param bool $ignoreCase
     * @return QString
     */
    function fromFirstOccurrenceOf($string, $includeSubStringInResult = false, $ignoreCase = false)
    {
        if ($ignoreCase)
            $index = $this->indexOfIgnoreCase((string)$string);
        else
            $index = $this->indexOf((string)$string);

        if ($index > -1) {
            if ($includeSubStringInResult)
                return $this->substring($index, $this->length());
            else
                return $this->substring($index + strlen((string)$string), $this->length());
        }

        return String("");
    }

    /**
     * @param $string
     * @param bool $includeSubStringInResult
     * @param bool $ignoreCase
     * @return QString
     */
    function fromLastOccurrenceOf($string, $includeSubStringInResult = false, $ignoreCase = false)
    {
        if ($ignoreCase)
            $index = $this->lastIndexOfIgnoreCase((string)$string);
        else
            $index = $this->lastIndexOf((string)$string);

        if ($index > -1) {
            if ($includeSubStringInResult)
                return $this->substring($index, $this->length());
            else
                return $this->substring($index + strlen((string)$string), $this->length());
        }

        return String("");
    }

    /**
     * @param $string
     * @param bool $includeSubStringInResult
     * @param bool $ignoreCase
     * @return QString
     */
    function upToFirstOccurrenceOf($string, $includeSubStringInResult = false, $ignoreCase = false)
    {
        if ($ignoreCase)
            $index = $this->indexOfIgnoreCase((string)$string);
        else
            $index = $this->indexOf((string)$string);

        if ($index > -1) {
            if ($includeSubStringInResult)
                return $this->substring(0, $index + strlen((string)$string));
            else
                return $this->substring(0, $index);

        }

        return static::create($this->text);
    }

    /**
     * @param $string
     * @param bool $includeSubStringInResult
     * @param bool $ignoreCase
     * @return QString
     */
    function upToLastOccurrenceOf($string, $includeSubStringInResult = false, $ignoreCase = false)
    {
        if ($ignoreCase)
            $index = $this->lastIndexOfIgnoreCase((string)$string);
        else
            $index = $this->lastIndexOf((string)$string);

        if ($index > -1) {
            if ($includeSubStringInResult)
                return $this->substring(0, $index + strlen((string)$string));
            else
                return $this->substring(0, $index);

        }

        return static::create($this->text);
    }

    /**
     * @return QString
     */
    function trim()
    {
        return static::create(\trim($this->text));
    }

    /**
     * @return QString
     */
    function trimStart()
    {
        return static::create(ltrim($this->text));
    }

    /**
     * @return QString
     */
    function trimEnd()
    {
        return static::create(rtrim($this->text));
    }

    /**
     * @param $character_mask
     * @return QString
     */
    function trimCharactersAtStart($character_mask)
    {
        return static::create(ltrim($this->text, (string)$character_mask));
    }

    /**
     * @param $character_mask
     * @return QString
     */
    function trimCharactersAtEnd($character_mask)
    {
        return static::create(rtrim($this->text, (string)$character_mask));
    }

    /**
     * @return QString
     */
    function toUpperCase()
    {
        return static::create(strtoupper($this->text));
    }

    /**
     * @return QString
     */
    function toLowerCase()
    {
        return static::create(strtolower($this->text));
    }

    /**
     * @param $startIndex
     * @param $length
     * @param $stringToInsert
     * @return QString
     */
    function replaceSection($startIndex, $length, $stringToInsert)
    {
        return static::create(substr_replace($this->text, (string)$stringToInsert, $startIndex, $length));
    }

    /**
     * @param $needles
     * @param $replacement
     * @param bool $ignoreCase
     * @return QString
     */
    function replace($needles, $replacement, $ignoreCase = false)
    {
        foreach ((array)$needles as $needle) {
            if ($ignoreCase)
                return static::create(str_ireplace($needle, $replacement, $this->text));

            return static::create(str_replace($needle, $replacement, $this->text));
        }
    }


    /**
     * @param $needles
     * @param $replacement
     * @param bool $ignoreCase
     * @return QString
     */
    function remove($needles, $ignoreCase = false)
    {
        $replacement = '';
        foreach ((array)$needles as $needle) {
            if ($ignoreCase)
                return static::create(str_ireplace($needle, $replacement, $this->text));

            return static::create(str_replace($needle, $replacement, $this->text));
        }
    }

    /**
     * @param $charactersToReplace
     * @param $charactersToInsertInstead
     * @return QString|QString
     */
    function replaceCharacters($charactersToReplace, $charactersToInsertInstead)
    {
        $searchedChars = str_split($charactersToReplace);
        $replaceChars = str_split($charactersToInsertInstead);

        if (count($searchedChars) != count($replaceChars))
            return $this->text;

        $characters = $this->toArray();

        foreach ($characters as $key => $char) {
            foreach ($searchedChars as $charKey => $searchedChar) {
                if ($searchedChar == $char) {
                    $characters[$key] = $replaceChars[$charKey];
                }
            }
        }

        return static::create(implode('', $characters));

    }

    /**
     * @param $charactersToRetain
     * @return QString|QString
     */
    function retainCharacters($charactersToRetain)
    {
        $searchedChars = str_split($charactersToRetain);

        if (empty($searchedChars))
            return $this->text;

        $characters = $this->toArray();
        $newChars = array();

        foreach ($characters as $key => $char) {
            foreach ($searchedChars as $searchedChar) {
                if ($searchedChar == $char) {
                    array_push($newChars, $char);
                }
            }
        }

        return static::create(implode('', $newChars));
    }

    /**
     * @param $charactersToRemove
     * @return QString|QString
     */
    function removeCharacters($charactersToRemove)
    {
        $searchedChars = str_split($charactersToRemove);

        if (empty($searchedChars))
            return $this->text;

        $characters = $this->toArray();

        foreach ($characters as $key => $char) {
            foreach ($searchedChars as $charKey => $searchedChar) {
                if ($searchedChar == $char) {
                    unset($characters[$key]);
                }
            }
        }

        return static::create(implode('', $characters));
    }

    /**
     * @param $needle
     * @param $replacement
     * @param bool $ignoreCase
     * @return QString
     */
    function replaceFirstOccurrenceOf($needle, $replacement, $ignoreCase = false)
    {
        if ($ignoreCase)
            $startIndex = $this->indexOfIgnoreCase($needle);
        else
            $startIndex = $this->indexOf($needle);

        if ($startIndex !== false) {
            $newstring = substr_replace($this->text, $replacement, $startIndex, strlen($needle));
            return static::create($newstring);
        }

        return static::create($this->text);
    }


    /**
     * @param $permittedCharacters
     * @return QString|QString
     */
    function initialSectionContainingOnly($permittedCharacters)
    {
        $searchedChars = str_split((string)$permittedCharacters);

        if (empty($searchedChars))
            return $this->text;

        $characters = $this->toArray();
        $newChars = array();

        foreach ($characters as $key => $char)
        {
            $shouldAdd = false;
            foreach ($searchedChars as $charKey => $searchedChar)
            {
                if ($searchedChar == $char)
                {
                    $shouldAdd = true;
                    break;
                }
            }

            if ($shouldAdd)
                array_push($newChars, $char);
            else
                break;
        }

        return static::create(implode('', $newChars));
    }

    /**
     * @param $charactersToStopAt
     * @return QString|QString
     */
    function initialSectionNotContaining($charactersToStopAt)
    {
        $searchedChars = str_split((string)$charactersToStopAt);

        if (empty($searchedChars))
            return $this->text;

        $characters = $this->toArray();
        $newChars = array();

        foreach ($characters as $key => $char) {
            foreach ($searchedChars as $charKey => $searchedChar) {
                if ($searchedChar == $char) {
                    break(2);
                }

            }
            array_push($newChars, $char);
        }

        return static::create(implode('', $newChars));
    }

    /**
     * @return bool
     */
    function isQuotedString()
    {
        if (strpos($this->text, '"') !== false || strpos($this->text, "'") !== false)
            return true;

        return false;
    }

    /**
     * @return QString
     */
    function unquoted()
    {
        $txt = str_replace('"', "", $this->text);
        $txt = str_replace("'", "", $txt);

        return static::create($txt);
    }

    /**
     * @param QString $quoteCharacter
     * @return QString
     */
    function quoted($quoteCharacter = '"')
    {
        return static::create((string)$quoteCharacter . $this->text . (string)$quoteCharacter);
    }

    /**
     * @param $string
     * @param $length
     * @return QString
     */
    function paddedLeft($string, $length)
    {
        return static::create(str_pad($this->text, $length, (string)$string, STR_PAD_LEFT));
    }

    /**
     * @param $string
     * @param $length
     * @return QString
     */
    function paddedRight($string, $length)
    {
        return static::create(str_pad($this->text, $length, (string)$string, STR_PAD_RIGHT));
    }

    /**
     * @param $string
     * @param $length
     * @return QString
     */
    function paddedBoth($string, $length)
    {
        return static::create(str_pad($this->text, $length, (string)$string, STR_PAD_BOTH));
    }

    /**
     *
     */
    function render($withBreakLine = true)
    {
        if ($withBreakLine)
            echo $this->text. '<br/>';
        else
            echo $this->text;
    }

    /**
     * @param $string
     * @return QString
     */
    function stripTags()
    {
        return static::create(strip_tags($this->text));
    }

    /**
     * @return int
     */
    function getIntValue()
    {
        return intval($this->text);
    }

    /**
     * @return float
     */
    function getFloatValue()
    {
        return floatval($this->text);
    }

    /**
     * @param $token
     * @return QString
     */
    function tokenize($token)
    {
        return static::create(strtok($this->text, (string)$token));
    }

    /**
     * @return bool
     */
    function getBoolValue()
    {
        return boolval($this->text);
    }

    /**
     * @param int $decimals
     * @param QString $dec_point
     * @param QString $thousands_sep
     * @return QString
     */
    function getDecimalValue($decimals = 2, $dec_point = ".", $thousands_sep = ",")
    {
        return number_format($this->getFloatValue(), $decimals, $dec_point, $thousands_sep);
    }

    /**
     * @return QString
     */
    function getHexValue()
    {
        return implode(unpack("H*", $this->text));
    }

    /**
     * @return null|QString|QString[]
     */
    function toUTF8()
    {
        return mb_convert_encoding($this->text, "UTF-8", "auto");
    }

    /**
     * @return null|QString|QString[]
     */
    function toUTF16()
    {
        return mb_convert_encoding($this->text, "UTF-16", "auto");
    }

    /**
     * @return null|QString|QString[]
     */
    function toUTF32()
    {
        return mb_convert_encoding($this->text, "UTF-32", "auto");
    }

    /**
     * @param QString $encoding
     * @return null|QString|QString[]
     */
    function convertEncoding($encoding = "UTF-8")
    {
        return mb_convert_encoding($this->text, $encoding, "auto");
    }

    /**
     * @param QString $string
     */
    function swapWith(QString &$string)
    {
        $a = $this->text;
        $b = $string->text;

        $this->text = $b;
        $string->text = $a;
    }

    /**
     * @param $string
     * @return int
     */
    function getLevenshteinDistance($string)
    {
        return levenshtein($this->text, (string)$string);
    }

    /**
     * @param $string
     * @return int
     */
    function calculateSimilarity($string)
    {
        return similar_text($this->text, (string)$string);
    }

    /**
     * @param $string
     * @param int $width
     * @param QString $break
     * @param bool $cut
     * @return QString
     */
    function wordwrap($string, $width = 75, $break = "\n", $cut = false)
    {
        return static::create(\wordwrap($string, $width, $break, $cut));
    }

    /**
     * @return QString
     */
    function htmlentities()
    {
        return static::create(\htmlentities($this->text));
    }

    /**
     * @return QString
     */
    function htmlspecialchars()
    {
        return static::create(\htmlspecialchars($this->text));
    }

    /**
     * Returns the character at $index, with indexes starting at 0.
     *
     * @param  int    $index Position of the character
     * @return static The character at $index
     */
    public function at($index)
    {
        return $this->substring($index, 1);
    }

    /**
     * @return QString
     */
    function quoteMetaTags()
    {
        return static::create(quotemeta($this->text));
    }

    /**
     * @return QString
     */
    function addCSlashes()
    {
        return static::create(addcslashes($this->text));
    }

    /**
     * @return QString
     */
    function addSlashes()
    {
        return static::create(addslashes($this->text));
    }

    /**
     * @return QString
     */
    function bin2hex()
    {
        return static::create(bin2hex($this->text));
    }

    /**
     * @return QString
     */
    function ucWords()
    {
        return static::create(ucwords($this->text));
    }

    /**
     * @return QString
     */
    function serialize()
    {
        return serialize($this->text);
    }

    /**
     * @return QString
     */
    function toJson()
    {
        return json_encode($this->text);
    }

    /**
     * @return QString
     */
    function toRot13()
    {
        return static::create(str_rot13($this->text));
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  QString  $title
     * @param  QString  $separator
     * @return QString
     */
    public function slug($separator = '-')
    {
        $title = static::ascii($this->text);
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);
        return static::create(trim($title, $separator));
    }

    /**
     * @return QString
     */
    public function stripPunctuation()
    {
        return static::create(preg_replace("#[[:punct:]]#", "", $this->text));
    }


    /**
     * Returns a lowercase and trimmed string separated by the given delimiter.
     * Delimiters are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces, dashes,
     * and underscores. Alpha delimiters are not converted to lowercase.
     *
     * @param  QString $delimiter Sequence used to separate parts of the string
     * @return static Object with a delimited $str
     */
    public function delimit($delimiter)
    {
        $regexEncoding = $this->regexEncoding();
        $this->regexEncoding($this->encoding);
        $str = $this->eregReplace('\B([A-Z])', '-\1', $this->trim());
        $str = \mb_strtolower($str, $this->encoding);
        $str = $this->eregReplace('[-_\s]+', $delimiter, $str);
        $this->regexEncoding($regexEncoding);
        return static::create($str, $this->encoding);
    }

    /**
     * @return QString
     */
    public function toAscii()
    {
        return static::create(static::ascii($this->text));
    }

    /**
     * Converts each tab in the string to some number of spaces, as defined by
     * $tabLength. By default, each tab is converted to 4 consecutive spaces.
     *
     * @param  int    $tabLength Number of spaces to replace each tab with
     * @return static Object whose $str has had tabs switched to spaces
     */
    public function toSpaces($tabLength = 4)
    {
        $spaces = str_repeat(' ', $tabLength);
        $str = str_replace("\t", $spaces, $this->text);
        return static::create($str);
    }

    /**
     * Converts each occurrence of some consecutive number of spaces, as
     * defined by $tabLength, to a tab. By default, each 4 consecutive spaces
     * are converted to a tab.
     *
     * @param  int    $tabLength Number of spaces to replace with a tab
     * @return static Object whose $str has had spaces switched to tabs
     */
    public function toTabs($tabLength = 4)
    {
        $spaces = str_repeat(' ', $tabLength);
        $str = str_replace($spaces, "\t", $this->text);
        return static::create($str);
    }

    /**
     * Converts the first character of each word in the string to uppercase.
     *
     * @return static Object with all characters of $str being title-cased
     */
    public function toTitleCase()
    {
        $str = \mb_convert_case($this->text, \MB_CASE_TITLE, $this->encoding);
        return static::create($str, $this->encoding);
    }
    /**
     * @return mixed|QString
     */
    public function toCamelCase()
    {
        $value = $this->text;

        if (isset(static::$camelCache[$value]))
        {
            return static::$camelCache[$value];
        }

        return static::create(static::$camelCache[$value] = lcfirst($this->toStudlyCase($value)));
    }

    /**
     * @return mixed|QString
     */
    public function toStudlyCase()
    {
        $value = $this->text;
        $key = $value;

        if (isset(static::$studlyCache[$key]))
        {
            return static::$studlyCache[$key];
        }
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::create(static::$studlyCache[$key] = str_replace(' ', '', $value));
    }

    /**
     * @param QString $delimiter
     * @return QString
     */
    public function toSnakeCase($delimiter = '_')
    {
        $value = $this->text;
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter]))
        {
            return static::$snakeCache[$key][$delimiter];
        }
        if (! ctype_lower($value))
        {
            $value = preg_replace('/\s+/u', '', $value);
            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value), 'UTF-8');
        }

        return static::create(static::$snakeCache[$key][$delimiter] = $value);
    }

    /**
     * @return QString
     */
    function toBase64UrlEncode()
    {
        return strtr(base64_encode($this->text), '+/=', '-_,');
    }

    /**
     * @return QString
     */
    function shuffle()
    {
        return static::create(str_shuffle($this->text));
    }

    /**
     * @return QString
     */
    function reverse()
    {
        return static::create(strrev($this->text));
    }

    /**
     * @param $rounds
     * @return QString
     */
    function repeat($rounds)
    {
        return static::create(str_repeat($this->text, $rounds));
    }

    /**
     * @param $delimiter
     * @return array
     */
    function explode($delimiter)
    {
        return explode($delimiter, $this->text);
    }

    /**
     * @return int
     */
    function getNumBytes()
    {
        return mb_strlen($this->text);
    }

    /**
     * @return int
     */
    function getWordCount()
    {
        return str_word_count($this->text);
    }

    /**
     * @return int
     */
    function getSentenceCount()
    {
        $string = $this->text;
        // Change all endings into dots
        $string = str_replace(array('!','?'), '.', $string);
        // Remove non essentials
        $string = preg_replace('/[^a-zA-Z0-9\.]/', '', $string);
        // Remove multiple sentence endings
        $string = preg_replace('/\.{2,}/', '.', $string);
        // Count sentence endings
        return substr_count($string, '.');
    }

    /**
     * @return int
     */
    function getNumBytesAsUTF8()
    {
        return mb_strlen($this->toUTF8());
    }

    /**
     * @return int
     */
    function getNumBytesAsUTF16()
    {
        return mb_strlen($this->toUTF16());
    }

    /**
     * @return int
     */
    function getNumBytesAsUTF32()
    {
        return mb_strlen($this->toUTF32());
    }

    /**
     * @return QString
     */
    function withHtmlLineBreak()
    {
        return static::create($this->text.'<br/>');
    }

    /**
     * @param int $limit
     * @param QString $end
     * @return QString|QString
     */
    public function truncate($limit = 100, $end = '...')
    {
        $value = $this->text;

        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return static::create(rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end);
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  QString  $value
     * @param  int     $words
     * @param  QString  $end
     * @return QString
     */
    public function truncateWords($words = 100, $end = '...')
    {
        $value = $this->text;
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);
        if (! isset($matches[0]) || mb_strlen($value) === mb_strlen($matches[0])) {
            return $value;
        }
        return static::create(rtrim($matches[0]).$end);
    }

    /**
     * Returns whether or not a character exists at an index. Offsets may be
     * negative to count from the last character in the string. Implements
     * part of the ArrayAccess interface.
     *
     * @param  mixed   $offset The index to check
     * @return boolean Whether or not the index exists
     */
    public function offsetExists($offset)
    {
        $length = $this->length();
        $offset = (int) $offset;
        if ($offset >= 0) {
            return ($length > $offset);
        }
        return ($length >= abs($offset));
    }
    /**
     * Returns the character at the given index. Offsets may be negative to
     * count from the last character in the string. Implements part of the
     * ArrayAccess interface, and throws an OutOfBoundsException if the index
     * does not exist.
     *
     * @param  mixed $offset         The index from which to retrieve the char
     * @return mixed                 The character at the specified index
     * @throws \OutOfBoundsException If the positive or negative offset does
     *                               not exist
     */
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        $length = $this->length();
        if (($offset >= 0 && $length <= $offset) || $length < abs($offset)) {
            throw new OutOfBoundsException('No character exists at the index');
        }
        return \mb_substr($this->text, $offset, 1, $this->encoding);
    }
    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of QString objects.
     *
     * @param  mixed      $offset The index of the character
     * @param  mixed      $value  Value to set
     * @throws \Exception When called
     */
    public function offsetSet($offset, $value)
    {
        $this->text[$offset] = (string)$value;
    }
    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of QString objects.
     *
     * @param  mixed      $offset The index of the character
     * @throws \Exception When called
     */
    public function offsetUnset($offset)
    {
        // Don't allow directly modifying the string
        unset($this->text[$offset]);
    }

    /**
     * Returns a new ArrayIterator, thus implementing the IteratorAggregate
     * interface. The ArrayIterator's constructor is passed an array of chars
     * in the multibyte string. This enables the use of foreach with instances
     * of QString\QString.
     *
     * @return \ArrayIterator An iterator for the characters in the string
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Replaces all occurrences of $pattern in $str by $replacement. An alias
     * for mb_ereg_replace(). Note that the 'i' option with multibyte patterns
     * in mb_ereg_replace() requires PHP 5.6+ for correct results. This is due
     * to a lack of support in the bundled version of Oniguruma in PHP < 5.6,
     * and current versions of HHVM (3.8 and below).
     *
     * @param  QString $pattern     The regular expression pattern
     * @param  QString $replacement The string to replace with
     * @param  QString $options     Matching conditions to be used
     * @return static Object with the resulting $str after the replacements
     */
    public function regexReplace($pattern, $replacement, $options = 'msr')
    {
        $regexEncoding = $this->regexEncoding();
        $this->regexEncoding($this->encoding);
        $str = $this->eregReplace($pattern, $replacement, $this->text, $options);
        $this->regexEncoding($regexEncoding);
        return static::create($str, $this->encoding);
    }

    /**
     * @param QString $newline
     */
    function hexDump($newline = "<br/>")
    {
        $data = $this->text;
        static $from = '';
        static $to = '';

        static $width = 16; # number of bytes per line

        static $pad = '.'; # padding for non-visible characters

        if ($from==='')
        {
            for ($i=0; $i<=0xFF; $i++)
            {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width*2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line)
        {
            echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }

    /**
     * Alias for mb_regex_encoding which default to a noop if the mbstring
     * module is not installed.
     */
    protected function regexEncoding()
    {
        static $functionExists;
        if ($functionExists === null) {
            $functionExists = function_exists('\mb_regex_encoding');
        }
        if ($functionExists) {
            $args = func_get_args();
            return call_user_func_array('\mb_regex_encoding', $args);
        }
    }

    /**
     * Alias for mb_ereg_replace with a fallback to preg_replace if the
     * mbstring module is not installed.
     */
    protected function eregReplace($pattern, $replacement, $string, $option = 'msr')
    {
        static $functionExists;
        if ($functionExists === null) {
            $functionExists = function_exists('\mb_split');
        }
        if ($functionExists) {
            return \mb_ereg_replace($pattern, $replacement, $string, $option);
        } else if ($this->supportsEncoding()) {
            $option = str_replace('r', '', $option);
            return \preg_replace("/$pattern/u$option", $replacement, $string);
        }
    }

    /**
     * Returns true if $str matches the supplied pattern, false otherwise.
     *
     * @param  QString $pattern Regex pattern to match against
     * @return bool   Whether or not $str matches the pattern
     */
    protected function matchesPattern($pattern)
    {
        $regexEncoding = $this->regexEncoding();
        $this->regexEncoding($this->encoding);
        $match = \mb_ereg_match($pattern, $this->text);
        $this->regexEncoding($regexEncoding);
        return $match;
    }

    /**
     * @return bool
     */
    protected function supportsEncoding()
    {
        $supported = ['UTF-8' => true, 'ASCII' => true];
        if (isset($supported[$this->encoding])) {
            return true;
        } else {
            throw new \RuntimeException('QString method requires the ' .
                'mbstring module for encodings other than ASCII and UTF-8. ' .
                'Encoding used: ' . $this->encoding);
        }
    }

    /**
     * @param $hexstr
     * @return QString
     */
    public static function fromHexValue($hexstr)
    {
        return static::create(pack("H*", $hexstr));
    }

    /**
     * @param $json
     * @return QString
     */
    public static function fromJson($json)
    {
        return static::create(json_decode($json));
    }

    /**
     * @param $string
     * @return QString
     */
    public static function fromRot13($string)
    {
        return static::create(str_rot13($string));
    }

    /**
     * @param $string
     * @return QString
     */
    public static function fromSerialized($string)
    {
        return static::create(unserialize($string));
    }

    /**
     * @param $string
     * @return QString
     */
    public static function fromBase64UrlEncoded($string)
    {
        return static::create(base64_decode(strtr($string, '-_,', '+/=')));
    }

    /**
     * @param $array
     * @return mixed
     */
    public static function convertAllStringsInArray($array)
    {
        $strings = $array;

        foreach ($strings as $key => $string)
        {
            $strings[$key] = static::create($string);
        }
        return $strings;
    }

    /**
     * @param $value
     * @return null|QString|QString[]
     */
    public static function ascii($value)
    {
        foreach (static::charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }
        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * @param QString $string
     * @param null $encoding
     * @return QString
     */
    public static function create($string = '', $encoding = null)
    {
        return new QString($string, $encoding);
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int  $length
     * @return QString
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return static::create( mb_substr(str_shuffle(str_repeat($pool, $length)), 0, $length));

    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return QString
     */
    public static function random($length = 16)
    {
        $string = '';
        while (($len = mb_strlen($string)) < $length)
        {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= mb_substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return static::create($string);
    }

    /**
     * @param $string
     * @param $rounds
     * @return QString
     */
    public static function repeatedString($string, $rounds)
    {
        return static::create(str_repeat($string , $rounds ));
    }

    /**
     * @param $string
     * @return \string
     */
    private static function overflow32($v)
    {
        $v = $v % 4294967296;
        if ($v > 2147483647)
            return $v - 4294967296;
        elseif ($v < - 2147483648)
            return $v + 4294967296;
        else
            return $v;
    }

    /**
     * @param $type
     * @return boolean
     */
    public function validate($type)
    {
        return StringValidator::validate($this->text, $type);
    }


    /**
     * @return string
     */
    public function toStdString()
    {
        return $this->text;
    }

    /**
     * @return QString
     */
    public function sanitizeSQL()
    {
        return static::create(Security::sanitize_sql_string($this->text));
    }

    /**
     * @return QString
     */
    public function sanitizeParanoid()
    {
        return static::create(Security::sanitize_paranoid_string($this->text));
    }

    /**
     * @return QString
     */
    public function sanitizeSystem()
    {
        return static::create(Security::sanitize_system_string($this->text));
    }

    /**
     * @return QString
     */
    public function sanitizeHtml()
    {
        return static::create(Security::sanitize_html_string($this->text));
    }

    /**
     * @return QString
     */
    public function sanitizeLDAP()
    {
        return static::create(Security::sanitize_ldap_string($this->text));
    }

    /**
     * @return \StdObject
     */
    public function decodeJson()
    {
        return json_decode($this->text);
    }

    /**
     * @return \StdObject
     */
    public function decodeBase64()
    {
        return static::create(base64_decode($this->text));
    }

        /**
     * @return array
     */
    protected static function charsArray()
    {
        static $charsArray;
        if (isset($charsArray)) {
            return $charsArray;
        }
        return $charsArray = [
            '0'    => ['°', '₀', '۰'],
            '1'    => ['¹', '₁', '۱'],
            '2'    => ['²', '₂', '۲'],
            '3'    => ['³', '₃', '۳'],
            '4'    => ['⁴', '₄', '۴', '٤'],
            '5'    => ['⁵', '₅', '۵', '٥'],
            '6'    => ['⁶', '₆', '۶', '٦'],
            '7'    => ['⁷', '₇', '۷'],
            '8'    => ['⁸', '₈', '۸'],
            '9'    => ['⁹', '₉', '۹'],
            'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'],
            'b'    => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
            'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
            'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
            'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'],
            'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
            'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
            'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
            'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'],
            'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
            'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
            'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
            'm'    => ['м', 'μ', 'م', 'မ', 'მ'],
            'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
            'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'],
            'p'    => ['п', 'π', 'ပ', 'პ', 'پ'],
            'q'    => ['ყ'],
            'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
            's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
            't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
            'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'],
            'v'    => ['в', 'ვ', 'ϐ'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
            'x'    => ['χ', 'ξ'],
            'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
            'aa'   => ['ع', 'आ', 'آ'],
            'ae'   => ['ä', 'æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'at'   => ['@'],
            'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['ö', 'œ', 'ؤ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ', 'ش'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'ue'   => ['ü'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ', 'ژ'],
            '(c)'  => ['©'],
            'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'],
            'B'    => ['Б', 'Β', 'ब'],
            'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
            'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
            'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'],
            'F'    => ['Ф', 'Φ'],
            'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
            'H'    => ['Η', 'Ή', 'Ħ'],
            'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'],
            'K'    => ['К', 'Κ'],
            'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
            'M'    => ['М', 'Μ'],
            'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
            'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'],
            'P'    => ['П', 'Π'],
            'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
            'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
            'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
            'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'],
            'V'    => ['В'],
            'W'    => ['Ω', 'Ώ', 'Ŵ'],
            'X'    => ['Χ', 'Ξ'],
            'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
            'AE'   => ['Ä', 'Æ', 'Ǽ'],
            'CH'   => ['Ч'],
            'DJ'   => ['Ђ'],
            'DZ'   => ['Џ'],
            'GX'   => ['Ĝ'],
            'HX'   => ['Ĥ'],
            'IJ'   => ['Ĳ'],
            'JX'   => ['Ĵ'],
            'KH'   => ['Х'],
            'LJ'   => ['Љ'],
            'NJ'   => ['Њ'],
            'OE'   => ['Ö', 'Œ'],
            'PS'   => ['Ψ'],
            'SH'   => ['Ш'],
            'SHCH' => ['Щ'],
            'SS'   => ['ẞ'],
            'TH'   => ['Þ'],
            'TS'   => ['Ц'],
            'UE'   => ['Ü'],
            'YA'   => ['Я'],
            'YU'   => ['Ю'],
            'ZH'   => ['Ж'],
            ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
            ];
        }



}