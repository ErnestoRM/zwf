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
// --------------------------------------------------------------------
// Deployment variables
// --------------------------------------------------------------------
// Paths
// ALL PATHS MUST END WITH A '/'.
$cfg['appPath'] = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
$cfg['basePath'] = $cfg['appPath'] . 'base/';
$cfg['cfgPath'] = $cfg['appPath'] . 'cfg/';
$cfg['controllerPath'] = $cfg['appPath'] . 'controllers/';
$cfg['libPath'] = $cfg['appPath'] . 'lib/';
$cfg['modelPath'] = $cfg['appPath'] . 'models/';
$cfg['viewPath'] = $cfg['appPath'] . 'views/';
$cfg['overloadPath'] = $cfg['appPath'] . 'overload/';

// URLs
// ALL URLS MUST END WITH A '/'.
$cfg['rootUrl'] = '/';

// Server Time Zone
$cfg['timeZone'] = 'Europe/Madrid';


// --------------------------------------------------------------------
// System behavior settings
// --------------------------------------------------------------------
// Show PHP errors
$cfg['showErrors'] = true;

// Default init function to be called when load controller
$cfg['defaultControllerInitFunction'] = '_init';

// Default function to be called when load controller
$cfg['defaultControllerFunction'] = '_main';

// Default controller class to be used when visiting root.
$cfg['defaultController'] = 'Index';

// Fallback controller class. If defined, use it in case of controller not found.
$cfg['fallbackController'] = '';

// zfx is default namespace
$cfg['zfxNamespace'] = false;

// Prefix in model names
$cfg['modelPrefix'] = "M";

// --------------------------------------------------------------------
// Configuration auto-load
// --------------------------------------------------------------------
// Example: $cfg['autoLoadConfig'] = array('modules.php', 'myappcfg.php');
$cfg['autoLoadConfig'] = null;


// --------------------------------------------------------------------
// Modules (sorted by load preference)
// --------------------------------------------------------------------

$cfg['enabledModules'] = array(
    'zfx' => array('core', 'dev', 'data-access', 'app', 'data-model'),
);

// --------------------------------------------------------------------
// Localizer
// --------------------------------------------------------------------
// Location of i18n strings
$cfg['i18nPath'] = $cfg['cfgPath'];

// Available (enabled) languages.
$cfg['languages'] = array
    (
    'es', 'en'
);

// Default language
$cfg['defaultLanguage'] = 'es';

// Locale info
$cfg['languageInfo'] = array
    (
    'en' => array
        (
        'name'     => 'English',
        'true'     => 'yes',
        'false'    => 'no',
        'null'     => '(null)',
        'dec'      => '.',
        'sep'      => ',',
        'date'     => 'm-d-Y',
        'time'     => 'h:i:s a',
        'dateTime' => 'm-d-Y h:i:s a'
    ),
    'es' => array
        (
        'name'     => 'Español',
        'true'     => 'sí',
        'false'    => 'no',
        'null'     => '(nulo)',
        'dec'      => ',',
        'sep'      => '.',
        'date'     => 'd/m/Y',
        'time'     => 'H:i:s',
        'dateTime' => 'd/m/Y H:i:s'
    )
);

// Cache i18n sections?
$cfg['i18n_cache'] = true;
