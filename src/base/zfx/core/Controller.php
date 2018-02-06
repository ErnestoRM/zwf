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
 * Base controller class
 */
class Controller
{

    protected $_segments = null;

    // --------------------------------------------------------------------

    /**
     * Set URL segments
     *
     * @param array $segments List of strings (usually created by the front controller index.php).
     */
    public function _setSegments($segments)
    {
        if (!is_array($segments)) {
            $segments = null;
        }
        $this->_segments = $segments;
    }
    // --------------------------------------------------------------------

    /**
     * Get one or all URL segments
     *
     * @param int|null $num Segment offset (0-start) or NULL to retrieve all segments as array
     *
     * @return string|array
     */
    public function _segment($num = null)
    {
        if ($num === null) {
            return $this->_segments;
        } else {
            return a($this->_segments, $num);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get segment count
     */
    public function _segmentCount()
    {
        return count($this->_segments);
    }
    // --------------------------------------------------------------------

    /**
     * Get current controller URL
     *
     * Controller only: no segments
     *
     * @return string
     */
    public function _urlController()
    {
        $cl = get_class($this);
        if ($cl == Config::get('defaultController')) {
            return Config::get('rootUrl');
        } else {
            return Config::get('rootUrl') . StrFilter::dashes($cl) . '/';
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get current URL
     *
     * This function does not use data provided by web server ($_SERVER).
     * The current URL is computed using the controller instance data.
     *
     * @param integer $length Segment Limit. NULL
     *
     * @return string
     */
    public function _urlPath($length = null)
    {
        if ($length === 0 || !$this->_segment()) {
            return $this->_urlController();
        } else {
            $seg = array_slice($this->_segment(), 0, $length, true);

            return $this->_urlController() . (count($seg) > 0 ? implode('/', $seg) : '') . '/';
        }
    }
    // --------------------------------------------------------------------

    /**
     * Dispatch first segment
     *
     * Get the first URL segment. If a public function with that name is found, then call it.
     * The remaining segments will be passed as parameters.
     *
     * @param string $seg If NULL, the first URL segment will be tested.
     *                    If not null, try executing a function call $seg instead.
     *
     * @return boolean TRUE on success
     */
    public function _autoexec($seg = null)
    {
        if ($seg == null) {
            $seg = $this->_testAutoexec();
        }
        if ($seg != null) {
            if ($this->_segmentCount() > 1) {
                $params = $this->_segment();
                array_shift($params);
                call_user_func_array(array($this, $seg), $params);
            } else {
                call_user_func(array($this, $seg));
            }
            return true;
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Test first segment in order to be dispatched
     *
     * Get the first URL segment. If a public function with that name is found, then return it, or NULL.
     *
     * @return string
     */
    public function _testAutoexec()
    {
        if (StrValidator::segFunction($this->_segment(0))) {
            $seg = StrFilter::camelCase($this->_segment(0));
        } else {
            return null;
        }
        if (!$seg) {
            return null;
        }
        try {
            $method = new \ReflectionMethod($this, $seg);
        } catch (\ReflectionException $re) {
            return null;
        }
        if (($method->isPublic() || $method->isProtected()) && !($method->isStatic() || $method->isAbstract() || $method->isConstructor() || $method->isDestructor() || $method->isInternal() || $method->isClosure())) {
            return ($seg);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Send location header.
     *
     * The effect is redirect to other URL. Stops script execution.
     * The default location is root.
     *
     * @param string|NULL $location URL to go in
     */
    public static function _redirect($location = '')
    {
        $location = (string) $location;
        if ($location === '') {
            $location = Config::get('rootUrl');
        }
        header("Location: $location");
        die;
    }
    // --------------------------------------------------------------------

    /**
     * Send location header to controller.
     *
     * The effect is redirect to other URL. Stops script execution.
     * The default location is root.
     *
     * @param string|NULL $controller URL to go in
     */
    public static function _redirectController($controller = '', $segments = '')
    {
        $controller = (string) $controller;
        $segments = (string) $segments;
        if ($controller === '') {
            $controller = Config::get('rootUrl');
        }
        header("Location: " . Config::get('rootUrl') . $controller . '/' . $segments);
        die;
    }

    // --------------------------------------------------------------------

}
