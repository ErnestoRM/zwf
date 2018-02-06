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
 * Core Framework functions
 *
 * @package core
 */
namespace zfx;

/**
 * Array access wrapper
 *
 * Returns the value of specified $key.
 * Returns $noValue (null by default) if specified $key does not exist or
 * $array is not an array.
 */
function a($array, $key, $noValue = null)
{
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    } else {
        return $noValue;
    }
}
// --------------------------------------------------------------------

/**
 * Nested array access wrapper
 *
 * @param $array
 * @param $key1 , $key2, ... (variable parameters)
 *
 * @return The value of $array[$key1[$key2[...]]] or NULL if not found
 */
function aa()
{
    $keys = func_get_args();
    $array = array_shift($keys);

    if (!is_array($array)) {
        return null;
    }

    if (!isset($array[$keys[0]])) {
        return null;
    }

    if (count($keys) == 1) {
        $ret = $array[$keys[0]];
    } else {
        $keys[0] = $array[$keys[0]];
        $ret = call_user_func_array('\zfx\aa', $keys);
    }
    return $ret;
}
// --------------------------------------------------------------------

/**
 * Array tester

 * Tests that $array is defined, IS an array AND is not empty
 */
function va(&$array)
{
    if (isset($array) && is_array($array) && count($array) > 0) {
        return true;
    } else {
        return false;
    }
}
// --------------------------------------------------------------------

/**
 * Checks that $array[$key] exists AND is not empty.
 */
function av($array, $key)
{
    if (is_array($array) && isset($array[$key]) && !trueEmpty($array[$key])) {
        return true;
    } else {
        return false;
    }
}
// --------------------------------------------------------------------

/**
 * Checks that $var is empty.
 *
 * @param type $var
 *
 * @return boolean TRUE if empty (0 = not empty).
 */
function trueEmpty($var)
{
    if (is_numeric($var)) {
        return false;
    } else {
        return empty($var);
    }
}
// --------------------------------------------------------------------

/**
 * URL Controller name format test
 */
function getController($string)
{
    if (preg_match('/^[a-z][-0-9a-z]*$/u', $string) == 1) {
        return $string;
    } else {
        return null;
    }
}
// --------------------------------------------------------------------

/**
 * URL Segment format test
 */
function validSegment($string)
{
    if (preg_match('/^[0-9a-z](?:[-0-9a-z]*?[0-9a-z])?$/u', $string) == 1) {
        return true;
    } else {
        return false;
    }
}
// --------------------------------------------------------------------

/**
 * Controller not found handler
 *
 * @param string $className Class tried
 */
function controllerNotFound($className)
{
    echo "Controller not found: $className";
    die;
}
// --------------------------------------------------------------------

/**
 * Load base class
 *
 * @param string $className
 */
function loadBaseClass($className)
{
    $file = Config::get('basePath') . $className . '.php';
    require_once($file);
}
// --------------------------------------------------------------------

/**
 * Load controller class
 *
 * @param string $className
 */
function loadControllerClass($className)
{
    $file = Config::get('controllerPath') . $className . '.php';
    require_once($file);
}
// ------------------------------------------------------------------------

/**
 * Class Autoloader Function
 *
 * @param string $className Name of the class
 */
function _loadClass($className)
{
    // Split into namespace and classname
    $pos = strrpos($className, '\\');
    if ($pos !== false) {
        $namespace = substr($className, 0, $pos);
        $file = substr($className, $pos + 1) . '.php';

        $overloadPath = Config::get('overloadPath') . $file;
        if (file_exists($overloadPath)) {
            require_once($overloadPath);
            return;
        } else {
            $modules = a(Config::get('enabledModules'), $namespace);

            if ($modules) {
                foreach ($modules as $module) {
                    $completePath = Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $file;
                    if (file_exists($completePath)) {
                        require_once($completePath);
                        return;
                    } else {
                        if (Config::get('dbSys')) {
                            $completePath = Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . Config::get('dbSys') . DIRECTORY_SEPARATOR . $file;
                            if (file_exists($completePath)) {
                                require_once($completePath);

                                return;
                            }
                        }
                    }
                }
            }
        }
    } else {
        $file = $className . '.php';
        require_once($file);
    }
}
spl_autoload_register('\\zfx\\_loadClass');



