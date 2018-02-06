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
 * View class: simple html template handler
 */
class View
{

    /**
     *
     * @var array $sectionViews
     */
    private $sectionViews;

    /**
     *
     * @var array $sectionData
     */
    private $sectionData;

    /**
     *
     * @var array $sectionViewsSpool
     */
    private $sectionViewsSpool;

    /**
     *
     * @var array $sectionDataSpool
     */
    private $sectionDataSpool;

    /**
     *
     * @var string $templatePath
     */
    private $templatePath;
    private $globalData;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param $templateFile If NULL, sections will be added and showed as FIFO.
     *                      If a template file is provided then is expected the template file
     *                      to include sections using $this->section('id-seccion'); code.
     */
    public function __construct($templateFile = null, $globalData = null)
    {
        $this->sectionViews = array();
        $this->sectionData = array();
        $this->sectionViewsSpool = array();
        $this->sectionDataSpool = array();
        if (va($globalData)) {
            $this->globalData = $globalData;
        } else {
            $this->globalData = array();
        }
        if ($templateFile == null) {
            $this->templatePath = null;
        } else {
            $path = self::viewFile2Path($templateFile);
            if ($path) {
                $this->templatePath = $path;
            } else {
                Debug::devError(__METHOD__ . ' - Template view not found: ' . $templateFile);
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Add section to template
     *
     * @param string $id        Section ID or NULL if no template is available.
     * @param string $viewFile  Section filename
     * @param array  $dataArray Section data (var => value, ...)
     */
    public function addSection($id, $viewFile, $dataArray = null)
    {
        if (self::viewFile2Path($viewFile)) {
            if ($id === null) {
                $this->sectionViewsSpool[] = $viewFile;
                $this->sectionDataSpool[] = $dataArray;
            } else if (StrValidator::viewSectionID($id)) {
                $this->sectionViews[$id][] = $viewFile;
                $this->sectionData[$id][] = $dataArray;
            } else {
                Debug::devError(__METHOD__ . ' - Invalid section ID: ' . $id);
            }
        } else {
            Debug::devError(__METHOD__ . ' - Section view not found: ' . $viewFile);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Build and show view
     *
     * @param array $dataArray Template data
     */
    public function show($dataArray = null)
    {
        if (va($this->globalData)) {
            foreach ($this->globalData as $k => $v) {
                $$k = $v;
            }
        }
        if ($this->templatePath) {
            if (va($dataArray)) {
                foreach ($dataArray as $k => $v) {
                    $$k = $v;
                }
            }
            require($this->templatePath);
        } else {
            if (count($this->sectionViewsSpool)) {
                foreach ($this->sectionViewsSpool as $idc => $viewChunk) {
                    self::direct($viewFile, array_merge($this->globalData, $this->sectionDataSpool[$idc]));
                }
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Invoke section here
     *
     * This method must be called from the template.
     *
     * @param string $id Section ID
     *
     * @see addSection()
     */
    public function section($id)
    {
        if (a($this->sectionViews, $id)) {
            foreach ($this->sectionViews[$id] as $ids => $sectionChunks) {
                $this->showSection($sectionChunks, a($this->sectionData[$id], $ids));
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Inmediately show a view file using view instance context.
     *
     * @param array $dataArray View data (var => value, ...)
     */
    protected function showSection($viewFile, $dataArray = null)
    {
        $path = self::viewFile2Path($viewFile);

        if ($path) {
            if (va($this->globalData)) {
                foreach ($this->globalData as $k => $v) {
                    $$k = $v;
                }
            }
            if (va($dataArray)) {
                foreach ($dataArray as $k => $v) {
                    $$k = $v;
                }
            }
            require($path);
        } else {
            Debug::devError(__METHOD__ . ' - View not found: ' . $viewFile);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Inmediately show a view file.
     *
     * @param array $dataArray View data (var => value, ...)
     */
    public static function direct($viewFile, $dataArray = null)
    {
        $path = self::viewFile2Path($viewFile);

        if ($path) {
            if (va($dataArray)) {
                foreach ($dataArray as $k => $v) {
                    $$k = $v;
                }
            }
            require($path);
        } else {
            Debug::devError(__METHOD__ . ' - View not found: ' . $viewFile);
        }
    }
    // --------------------------------------------------------------------

    /**
     * View file availability test
     *
     * @param string $viewFile File name (without extension)
     *
     * @return string The complete path of the file.
     */
    private static function viewFile2Path($viewFile)
    {
        $fields = explode('@', $viewFile);
        $name = $fields[0];

        if (a($fields, 1)) {
            $path = Config::get('basePath') . $fields[1] . '/views/' . $name . '.php';
        } else {
            $path = Config::get('viewPath') . $name . '.php';
        }
        if (file_exists($path)) {
            return $path;
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------


}
