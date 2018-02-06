<?php
/*
  Zerfrex(tm) Web Framework.

  Copyright (c) Jorge A. Montes Pérez <jorge@zerfrex.com>
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  1. Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
  2. Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
  3. Neither the name of copyright holders nor the names of its
  contributors may be used to endorse or promote products derived
  from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
  TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
  PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
  BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @package core
 */
namespace zfx;

/**
 * Function provider class of UTF-8 string processing
 */
class StrFilter
{
    // --------------------------------------------------------------------

    /**
     * Convert to lower case
     *
     * @param $str String to be converted
     */
    public static function lowerCase($str)
    {
        return mb_convert_case($str, MB_CASE_LOWER, 'UTF-8');
    }
    // --------------------------------------------------------------------

    /**
     * Convert to upper case
     *
     * @param $str String to be converted
     */
    public static function upperCase($str)
    {
        return mb_convert_case($str, MB_CASE_UPPER, 'UTF-8');
    }
    // --------------------------------------------------------------------

    /**
     * Convert to title case (capitalize first letter of each word)
     *
     * @param $str String to be converted
     */
    public static function titleCase($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    }
    // --------------------------------------------------------------------

    /**
     * Single quote string
     *
     * @param $str String to be quoted
     * @param $escape Escape char. Set '' to disable; \ to use unix-style, ' to double quote (resulting That''s it).
     */
    public static function quote($str, $escape = '')
    {
        if ($escape != '') {
            $str = preg_replace(array("%'%u"), array($escape . "'"), $str);
        }
        return '\'' . $str . '\'';
    }
    // --------------------------------------------------------------------

    /**
     * Double quote string
     *
     * @param $str String to be quoted
     * @param $escape Escape char. Set '' to disable; \ to use unix-style, " to double quote (resulting Say ""yes"").
     */
    public static function doubleQuote($str, $escape = '')
    {
        if ($escape != '') {
            $str = preg_replace(array('%"%u'), array($escape . '"'), $str);
        }
        return '"' . $str . '"';
    }
    // --------------------------------------------------------------------

    /**
     * MySQL Escape String
     *
     * @param $str String to be escaped
     */
    public static function escapeMySQL($str)
    {
        $ret = '';
        $forbiddenChars = array(
            "\x00",
            "\n",
            "\r",
            '\\',
            "'",
            '"',
            "\x1a"
        );
        $replacement = array(
            '\\0',
            '\\n',
            '\\r',
            '\\\\',
            "\\'",
            '\\"',
            '\\Z'
        );
        $stringLength = mb_strlen($str);
        for ($i = 0; $i < $stringLength; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $key = array_search($char, $forbiddenChars);
            if ($key !== FALSE) {
                $ret .= $replacement[$key];
            } else {
                $ret .= $char;
            }
        }
        return $ret;
    }
    // --------------------------------------------------------------------

    /**
     * HTML escape string
     *
     * @param $str String to be escaped
     */
    public static function HTMLencode($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    // --------------------------------------------------------------------

    /**
     * HTML un-escape string
     *
     * @param $str String to be un-escaped
     */
    public static function HTMLdecode($str)
    {
        return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    // --------------------------------------------------------------------

    /**
     * Remove blanks both sides
     *
     * @param $str String to be cleaned
     */
    public static function spaceClear($str)
    {
        $str = preg_replace('/(^\s+)|(\s+$)/us', '', $str);
        return $str;
    }
    // --------------------------------------------------------------------

    /**
     * Remove all blanks
     *
     * @param $str String to be cleaned
     */
    public static function spaceClearAll($str)
    {
        $ret = preg_replace('/\s/u', '', $str);
        return $ret;
    }
    // --------------------------------------------------------------------

    /**
     * Compress blanks
     *
     * Two or more blanks become a single blank character
     *
     * @param $str String to be cleaned
     */
    public static function spaceCompress($str)
    {
        $ret = preg_replace('/((\s){2,})/u', ' ', $str);
        return $ret;
    }
    // --------------------------------------------------------------------

    /**
     * Apply filter chain
     *
     * @param $str String to be filtered
     * @param $filterList Ordered array with StrFilter method names that
     * will be applied
     */
    public static function filter($str, $filterList)
    {
        foreach ($filterList as $proc) {
            $str = self::$proc($str);
        }
        return $str;
    }
    // --------------------------------------------------------------------

    /**
     * String simplification
     *
     * Simplifies a string keeping letters and numbers and removing all
     * other characters.
     *
     * Translates the following spanish characters:
     * áéíóúàèìòùñüç   >>>  aeiouaeiounus
     *
     * It converts to lower case, deletes side blanks and compress inner blanks.
     * The remaning blanks are converted to dashes or the specified string.
     *
     * Any other character is deleted.
     *
     * @param type $str String to be simplified
     * @param string $spaceSub Allows only '-' (default), '_' or ''.
     * @return string
     */
    public static function getID($str, $spaceSub = '-')
    {
        if ($spaceSub != '_' && $spaceSub != '-' && $spaceSub != '') {
            $spaceSub = '-';
        }

        $str = self::filter($str, array(
                'lowerCase',
                'spaceClear'
        ));

        // Character substitution
        $patterns = array(
            '/ /u',
            '/á/u',
            '/é/u',
            '/í/u',
            '/ó/u',
            '/ú/u',
            '/à/u',
            '/è/u',
            '/ì/u',
            '/ò/u',
            '/ù/u',
            '/ñ/u',
            '/ç/u',
            '/ü/u',
            '/[^' . $spaceSub . 'a-z0-9]/u'
        );

        $replacements = array(
            $spaceSub,
            'a',
            'e',
            'i',
            'o',
            'u',
            'a',
            'e',
            'i',
            'o',
            'u',
            'n',
            's',
            'u',
            ''
        );
        $ret = preg_replace($patterns, $replacements, $str);
        if ($spaceSub != '') {
            $ret = preg_replace('/' . $spaceSub . '{2,}/u', $spaceSub, $ret);
            $ret = preg_replace('/$' . $spaceSub . '+/u', '', $ret);
            $ret = preg_replace('/' . $spaceSub . '+^/u', '', $ret);
        }
        return $ret;
    }
    // --------------------------------------------------------------------

    /**
     * Encode 4-bytes unicode string to alphanumeric string.
     *
     * @param $string String to be encoded
     * @return $string
     *
     * @see safeDecode()
     */
    public static function safeEncode($string)
    {
        $target = 'byte4be';
        $bytes = mb_convert_encoding($string, $target, 'UTF-8');
        $count = mb_strlen($bytes, $target);
        $ret = '';
        for ($i = 0; $i < $count; $i++) {
            $byte = mb_substr($bytes, $i, 1, $target);
            $ret .= sprintf("%02x", intval(ord($byte), 32));
        }
        return preg_replace_callback('/(0{2,9})/', function($m) {
            return chr(101 + strlen($m[1]));
        }, $ret);
    }
    // --------------------------------------------------------------------

    /**
     * Decodes a previously encoded string to a 4-byte unicode string.
     *
     * @param string $string String to be decoded
     * @return string
     *
     * @see safeEncode()
     */
    public static function safeDecode($string)
    {
        $string = preg_replace_callback('/([g-n])/', function($m) {
            return str_repeat('0', ord($m[1]) - 101);
        }, $string);
        $ret = '';
        $count = strlen($string);
        for ($i = 0; $i < $count; $i = $i + 2) {
            $hex = substr($string, $i, 2);
            $ret .= chr(intval($hex, 16));
        }
        return mb_convert_encoding($ret, 'UTF-8', 'byte4be');
    }
    // --------------------------------------------------------------------

    /**
     * Dashes to camel
     *
     * @example: this-is-a-example-string => thisIsAExampleString
     * @param string $string
     * @return string
     */
    public static function camelCase($string, $upperFirst = false)
    {
        if ($upperFirst) {
            $conv = preg_replace('/-/u', ' ', $string);
            return preg_replace('/\s/u', '', mb_convert_case($conv, MB_CASE_TITLE, 'UTF-8'));
        } else {
            $conv = preg_replace('/-/u', ' ', preg_replace('/(^\s+)|(\s+$)/us', '', $string));
            $expanded = mb_convert_case('x' . $conv, MB_CASE_TITLE, 'UTF-8');
            return preg_replace('/\s/u', '', mb_substr($expanded, 1, mb_strlen($expanded, 'UTF-8'), 'UTF-8'));
        }
    }
    // --------------------------------------------------------------------

    /**
     * camelCase to lower-dashes
     * @example: thisIsAnExample => this-is-an-example
     * @param string $string
     * @return string
     */
    public static function dashes($string)
    {
        $size = mb_strlen($string, 'UTF-8');
        $string = mb_substr($string, 0, 1, 'UTF-8') . preg_replace('/(\p{Lu})/u', '-$1', mb_substr($string, 1, $size - 1, 'UTF-8'));
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }

    // --------------------------------------------------------------------

    public static function initials($string)
    {
        $initials = '';
        $parts = explode(' ', self::filter($string, array('upperCase', 'spaceClear', 'spaceCompress')));
        if (va($parts))
            foreach ($parts as $part)
                $initials .= mb_substr($part, 0, 1, 'UTF-8');
        return $initials;
    }
    // --------------------------------------------------------------------
}
