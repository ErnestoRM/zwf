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
namespace zfx;

/**
 * Localizer
 */
class Localizer
{

    /**
     * Language code
     * @var string $lang
     */
    private $lang;

    /**
     * Language info array
     * @var array
     */
    private $langInfo;

    /**
     * Currently loaded i18n string sections
     * @var array
     */
    private $i18nSections;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $lang Use this language instead of default system language.
     */
    public function __construct($lang = NULL)
    {
        if (trueEmpty($lang)) {
            $this->setLang($lang);
        } else {
            $this->lang = $lang;
            $this->langInfo = a(Config::get('languageInfo'), $this->lang);
        }
        $this->i18nSections = array();
    }
    // --------------------------------------------------------------------

    /**
     * Get localized representation of a boolean value.
     *
     * @param boolean $value
     * @return string
     */
    public function getBoolean($value)
    {
        if ($value) {
            return $this->langInfo['true'];
        } else {
            return $this->langInfo['false'];
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get localized representation of a NULL value.
     *
     * @return string
     */
    public function getNull()
    {
        return $this->langInfo['null'];
    }
    // --------------------------------------------------------------------

    /**
     * Get localized date of a \DateTime value
     *
     * @param \DateTime $value
     * @return string
     */
    public function getDate(\DateTime $value = NULL)
    {
        if (!$value instanceof \DateTime) {
            //
        } else {
            return $value->format($this->langInfo['date']);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get localized time of a \DateTime value
     *
     * @param \DateTime $value
     * @return string
     */
    public function getTime(\DateTime $value = NULL)
    {
        if (!$value instanceof \DateTime) {
            return NULL;
        } else {
            return $value->format($this->langInfo['time']);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get localized date and time of a \DateTime value
     * @param \DateTime $value
     * @return string
     */
    public function getDateTime(\DateTime $value = NULL)
    {
        if (!$value instanceof \DateTime) {
            return NULL;
        } else {
            return $value->format($this->langInfo['dateTime']);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get localized representation of a floating-point value.
     *
     * @param float $value Value to convert
     * @param integer $precision Number of precision digits
     * @param boolean $sep If TRUE, print thousand separator
     * @return string Representation
     */
    public function getFloat($value, $precision = 5, $sep = FALSE)
    {
        $value = round((float) $value, $precision);
        if ($sep) {
            return number_format($value, $precision, $this->langInfo['dec'], $this->langInfo['sep']);
        } else {
            return number_format($value, $precision, $this->langInfo['dec'], '');
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get current language set
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }
    // --------------------------------------------------------------------

    /**
     * Set current language
     *
     * @param string $lang
     */
    public function setLang($lang)
    {
        if (trueEmpty($lang) || !in_array($lang, Config::get('languages'))) {
            $this->lang = Config::get('defaultLanguage');
        } else {
            $this->lang = $lang;
        }
        $this->langInfo = a(Config::get('languageInfo'), $this->lang);
    }
    // --------------------------------------------------------------------

    /**
     * Get locale language info
     *
     * @return array
     */
    public function getLangInfo()
    {
        return $this->langInfo;
    }
    // --------------------------------------------------------------------

    /**
     * Set locale language info
     *
     * @param array $value
     */
    public function setLangInfo(array $value)
    {
        $this->langInfo = $value;
    }
    // --------------------------------------------------------------------

    /**
     * Convert a localized datetime value to a DateTime object
     *
     * @param string $value Localized datetime
     * @return DateTime or NULL if error
     */
    public function interpretDateTime($value)
    {
        $dt = \DateTime::createFromFormat($this->langInfo['dateTime'], $value);
        // Let's try with zero time too
        $d = \DateTime::createFromFormat($this->langInfo['date'], $value);
        if ($dt === FALSE) {
            if ($d === FALSE) {
                return NULL;
            } else {
                $d->setTime(0, 0, 0);
                return $d;
            }
        } else {
            return $dt;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Convert a localized time value to a DateTime object
     *
     * @param string $value Localized time
     * @return DateTime or NULL if error
     */
    public function interpretTime($value)
    {
        $dt = \DateTime::createFromFormat($this->langInfo['time'], $value);
        if ($dt === FALSE) {
            $dt = NULL;
        }
        return $dt;
    }
    // --------------------------------------------------------------------

    /**
     * Convert a localized date value to a DateTime object
     *
     * @param string $value Localized date
     * @return DateTime or NULL if error
     */
    public function interpretDate($value)
    {
        $dt = \DateTime::createFromFormat($this->langInfo['date'], $value);
        if ($dt === FALSE) {
            $dt = NULL;
        }
        return $dt;
    }

    // --------------------------------------------------------------------

    public function getString($section, $key, $fallBackValue = '')
    {
        if (!av($this->i18nSections, $section) || !Config::get('i18n_cache')) {
            $path = Config::get('cfgPath') . $this->lang . DIRECTORY_SEPARATOR . $section . '.php';
            if (file_exists($path)) {
                include($path);
                if (isset($i18n)) {
                    $this->i18nSections[$section] = $i18n;
                }
            }
        }
        $ret = aa($this->i18nSections, $section, $key);
        if (trueEmpty($ret)) {
            return $fallBackValue;
        }
        else {
            return $ret;
        }
    }
    // --------------------------------------------------------------------
}
