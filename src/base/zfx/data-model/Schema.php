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
 * @package data-source
 */
namespace zfx;

/**
 * Class Schema
 *
 * @package zfx
 */
class Schema
{

    //--------------------------------------------------------------------------------------------------------------
    /**
     * Array que contendrá las instancias de los modelos relacionados
     *
     * @var array
     */
    public $instances = [];
    //--------------------------------------------------------------------------------------------------------------
    /**
     * Nombre del campo de la clave primaria del modelo de datos
     *
     * @var
     */
    private $pKey;
    //--------------------------------------------------------------------------------------------------------------
    /**
     * Relación de campos disponibles en el modelo de datos
     *
     * @var array
     */
    private $fields = [];
    //--------------------------------------------------------------------------------------------------------------
    /**
     * Array que contendrá las relacions de la tabla.
     * Estructura:
     * 'tabla'=>'claveajena'
     *
     * @var array
     */
    private $relations = [];
    //--------------------------------------------------------------------------------------------------------------
    /**
     * @var array
     */
    private $indexed = [];

    //--------------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * @param array $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve todos los registros relacionados de la tabla especificada
     *
     * @param $table
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getRelated($table)
    {

        $instance = $this->getInstanceOf($table);

        if ($instance) {
            $PKey = $this->getpKey();
            $valuePkey = $this->getField($PKey);
            $Fkey = $this->relations[$table];

            return $instance->getBy($Fkey, $valuePkey);
        }

        return false;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Inicializa una instancia del modelo deseado en el array de instancias y lo devuelve.
     *
     * @param $table
     *
     * @return bool|mixed
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function getInstanceOf($table)
    {

        if (key_exists($table, $this->relations)) {
            if (!$this->instances[$table]) {
                $model = Config::get('modelPrefix') . \ucwords($table);
                $this->instances[$table] = new $model();
            }

            return $this->instances[$table];
        }

        return false;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function getPKey()
    {
        return $this->pKey;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $pKey
     */
    public function setPKey($pKey)
    {
        $this->pKey = $pKey;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve el valor de un campo específico
     *
     * @param $field
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getField($field)
    {
        $fields = $this->getFields();

        if (va($fields)) {
            return $fields[$field];
        }

        return false;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción:
     *
     * @return array
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getIndexed()
    {

        if ($this->indexed) {
            return $this->indexed;
        }
        $fields = $this->getFields();

        $this->setIndexed($fields);

        return $this->indexed;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción:
     *
     * @param $fields
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function setIndexed($fields)
    {

        foreach ($fields as $field => $data) {
            if ($data['index'] == true) {
                $this->indexed[$field] = $data['type'];
            }
        }
    }
    //--------------------------------------------------------------------------------------------------------------
}
