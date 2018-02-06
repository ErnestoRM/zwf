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
 * Configuration system class
 */
class Config
{

    // Store configuration data here
    private $sysConfig = array();
    // Singleton instance
    private static $instance = null;

    // --------------------------------------------------------------------

    /**
     * Get instance of Config class
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    // --------------------------------------------------------------------

    /**
     * Setup and initialize system
     */
    public static function setup()
    {
        // Load CORE config file
        self::loadConfig(__DIR__ . DIRECTORY_SEPARATOR . 'core-config.php', false);

        // Load CORE user settings
        self::loadConfig('core-config');

        // Error reporting
        if (!self::get('showErrors')) {
            error_reporting(0);
            ini_set('display_errors', '0');
        } else {
            error_reporting(2147483647);
            ini_set('display_errors', '1');
        }

        // Timezone set
        date_default_timezone_set(self::get('timeZone'));

        // Set INCLUDE_PATH and read enabled modules configuration
        if (self::get('enabledModules')) {
            foreach (self::get('enabledModules') as $namespace => $moduleList) {
                if ($moduleList) {
                    foreach ($moduleList as $module) {
                        if ($module != 'core') { // Skip CORE config
                            self::loadConfig(\zfx\Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'module-config.php', false);
                        }
                    }
                }
            }
        }

        // Load user configuration files
        if (is_array(self::get('autoLoadConfig'))) {
            foreach (self::get('autoLoadConfig') as $file) {
                self::loadConfig($file);
            }
        }

        // Finally. This must be the ending setup
        if (Config::get('dbSys')) {
            $dbAwareModelDir = \zfx\Config::get('modelPath') . Config::get('dbSys') . PATH_SEPARATOR;
        } else {
            $dbAwareModelDir = '';
        }
        set_include_path(get_include_path() . PATH_SEPARATOR . \zfx\Config::get('controllerPath') . PATH_SEPARATOR . \zfx\Config::get('controllerPath') . 'abstract' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . \zfx\Config::get('modelPath') . PATH_SEPARATOR . $dbAwareModelDir . \zfx\Config::get('libPath'));
    }
    // --------------------------------------------------------------------

    /**
     * Load configuration data from $file
     *
     * @param string $file File name (without .php extension if $computePath is TRUE)
     * @param boolean $computePath If TRUE, use system settings directory. If FALSE, $file should be a complete path specification
     */
    public static function loadConfig($file, $computePath = true)
    {
        $instance = self::getInstance();
        if ($computePath) {
            $path = $instance->sysConfig['cfgPath'] . $file . '.php';
        } else {
            $path = $file;
        }
        if (file_exists($path)) {
            include($path);
            if (isset($cfg)) {
                $instance->sysConfig = \array_merge($instance->sysConfig, $cfg);
            }
        }
    }
// --------------------------------------------------------------------

    /**
     * Get configuration data by key
     *
     * @param string $key Data key
     * @return mixed
     */
    public static function get($key)
    {
        // Function a() still not accesible here
        if (isset(self::getInstance()->sysConfig[$key]))
            return self::getInstance()->sysConfig[$key];
    }
// --------------------------------------------------------------------
}
