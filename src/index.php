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
// Load config and set things up
require('base/zfx/core/Config.php');
\zfx\Config::setup();
// Load core code (basic functions, class autoloading...)
require('base/zfx/core/core.php');


// Defaults
$request = null;
$controllerClass = \zfx\Config::get('defaultController');
$rawSegments = array();
$segments = null;


// Check if there is a request and get it
if (isset($_GET['_seg']) && !\zfx\trueEmpty($_GET['_seg'])) {
    $request = $_GET['_seg'];
} else if (isset($argc) && $argc == 2) {
    $request = $argv[1];
}

// If is there a request, look for a controller on it
if ($request) {
    $rawSegments = explode('/', $request);
    if (count($rawSegments) > 0) {
        $firstSegment = $rawSegments[0]; // Could be useful later
        $controllerClass = \zfx\getController($rawSegments[0]) ? array_shift($rawSegments) : \zfx\Config::get('defaultController');
    }
}

// Load and instantiate controller
// Convert dashes to CamelCase format
$fallbackControllerTried = false;
while (true) {
    $controllerClass = preg_replace('/\s/u', '', mb_convert_case(preg_replace('/-/u', ' ', $controllerClass), MB_CASE_TITLE, 'UTF-8'));
    $file = \zfx\Config::get('controllerPath') . $controllerClass . '.php';
    // What to do if specified controler class does not exist
    if (!file_exists($file)) {
        if ($fallbackControllerTried || \zfx\trueEmpty(\zfx\Config::get('fallbackController'))) {
            \zfx\controllerNotFound($controllerClass);
            break;
        } else {
            $controllerClass = \zfx\Config::get('fallbackController');
            $fallbackControllerTried = true;
        }
    } else {
        break;
    }
}

// Filter segments
if ($fallbackControllerTried) {
    // Recover deleted first segment
    if (\zfx\validSegment($firstSegment)) {
        $segments[] = $firstSegment;
    }
}
foreach ($rawSegments as $s) {
    if (\zfx\validSegment($s)) {
        $segments[] = $s;
    }
}

// Load specified controller class (and execute default controller function)
require_once($file);
if (\zfx\Config::get('zfxNamespace')) {
    $controllerClass = '\\zfx\\' . $controllerClass;
}

$controller = new $controllerClass();
$controller->_setSegments($segments);
if (method_exists($controller, \zfx\Config::get('defaultControllerInitFunction'))) {
    call_user_func(array($controller, \zfx\Config::get('defaultControllerInitFunction')));
}
if (method_exists($controller, \zfx\Config::get('defaultControllerFunction'))) {
    call_user_func(array($controller, \zfx\Config::get('defaultControllerFunction')));
}

// End of script
