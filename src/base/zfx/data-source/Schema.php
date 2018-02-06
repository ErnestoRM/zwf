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
 * Relation Schema
 *
 * This class serves as a database table or view scheme representation
 */
class Schema
{

    /**
     * Primary keys list
     *
     * @var array $primaryKey
     */
    private $primaryKey;

    /**
     * List of Fields (columns)
     *
     * @var array $fields
     */
    //private $fields;

    /**
     * List of Foreign Keys
     *
     * @var array $fks
     */
    private $fks;

    /**
     * List of related tables (tables that have foreign keys pointing to us)
     *
     * @var array $relTables
     */
    private $relTables;

    /**
     * Name of the table or view (relation)
     *
     * @var string $relation
     */
    private $relation;

    // --------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->primaryKey = array();
        $this->fields = array();
        $this->relTables = array();
        $this->fks = array();
    }
    // --------------------------------------------------------------------
    // Access methods
    // --------------------------------------------------------------------

    /**
     * Get relation name (name of the table or view)
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relation;
    }
    // --------------------------------------------------------------------

    /**
     * Set relation name
     *
     * @param string $value
     */
    public function setRelationName($value)
    {
        $this->relation = (string) $value;
    }
    // --------------------------------------------------------------------

    /**
     * Get field info list
     *
     * @return array
     */
    /* public function getFields()
      {
      return $this->fields;
      } */

    // --------------------------------------------------------------------

    /**
     * Set field info list
     *
     * @param $array Map array. Use column names as keys
     */
    /* public function setFields(array $fields)
      {
      $this->fields = array_merge($this->fields, $fields);
      // Do not accept NULL field IDs
      if (isset($this->fields[''])) {
      unset($this->fields['']);
      }
      } */

    // --------------------------------------------------------------------

    /**
     * Get foreign keys list
     *
     * @return array
     */
    public function getFks()
    {
        return $this->fks;
    }
    // --------------------------------------------------------------------

    /**
     * Set foreign keys list
     *
     * @param array $value
     */
    public function setFks(array $value)
    {
        $this->fks = $value;
    }
    // --------------------------------------------------------------------

    /**
     * Get related tables list. A related table is a table that has foreign
     * key restrictions pointing to us.
     *
     * @return array
     */
    public function getRelTables()
    {
        return $this->relTables;
    }
    // --------------------------------------------------------------------

    /**
     * Set related tables list. A related table is a table that has foreign
     * key restrictions pointing to us.
     *
     * @param array $val
     */
    public function setRelTables(array $val)
    {
        $this->relTables = $val;
    }
    // --------------------------------------------------------------------

    /**
     * Set field definition
     *
     * @param string     $fieldId   Field ID (usually name of column)
     *
     * @param \zfx\Field $fieldInfo Field information object
     */
    public function setField($fieldId, \zfx\Field $fieldInfo)
    {
        if (!trueEmpty($fieldId) && $fieldInfo) {
            $this->fields[$fieldId] = $fieldInfo;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get field definition
     *
     * Use this method in order to access a field without getting the whole list
     *
     * @param string $fieldId
     *
     * @return \zfx\Field
     */
    /*  public function getField($fieldId)
      {
      return a($this->fields, $fieldId);
      } */

    // --------------------------------------------------------------------

    /**
     * Set field as indexed
     *
     * @param string $fieldId Field ID
     */
    public function setIndex($fieldId)
    {
        if (a($this->fields, $fieldId)) {
            $this->fields[$fieldId]->setIndex(true);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Count fields
     *
     * @return integer
     */
    public function count()
    {
        return count($this->fields);
    }
    // --------------------------------------------------------------------
    // Delegation and convenience methods
    // --------------------------------------------------------------------

    /**
     * Remove some fields
     *
     * @param array $fieldIds List of field keys to be deleted
     *
     * @see removeFieldsBut()
     */
    public function removeFields(array $fieldIds)
    {
        foreach ($fieldIds as $f) {
            unset($this->fields[$f]);
        }
    }

    /**
     * Remove all fields except some
     *
     * @param array $fieldIds List of field keys to keep
     */
    public function removeFieldsExcept(array $fieldIds)
    {
        foreach (array_keys($this->fields) as $k) {
            if (!in_array($k, $fieldIds)) {
                unset($this->fields[$k]);
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Check if field exists
     *
     * @param string $fieldID Field ID
     *
     * @return boolean
     */
    public function checkField($fieldID)
    {
        return (isset($this->fields[$fieldID]));
    }
    // --------------------------------------------------------------------

    /**
     * Required field test
     *
     * Tests a input record. If there are in scheme required fields that
     * are not present in that record, returns an array of missing fields IDs.
     *
     * @param array $fieldSet Record set
     *
     * @return array Empty if all required fields are present
     */
    public function checkReqFieldSet($fieldSet)
    {
        $required = array();
        foreach ($this->fields as $id => $field) {
            if ($field->getRequired() && !in_array($id, $fieldSet) && !$field->getAuto()) {
                $required[] = $id;
            }
        }

        return $required;
    }
    // --------------------------------------------------------------------

    /**
     * Extract primary key list from record
     *
     * @param array $recordSet
     *
     * @return array subrecord
     */
    public function extractPk($recordSet)
    {
        $pk = array();
        foreach ($this->getPrimaryKey() as $k) {
            if (a($recordSet, $k)) {
                $pk[$k] = $recordSet[$k];
            } else {
                return null;
            }
        }

        return $pk;
    }
    // --------------------------------------------------------------------

    /**
     * Get primary keys list
     *
     * @return array
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    // --------------------------------------------------------------------

    /**
     * Set primary keys list
     *
     * @param array $val
     */
    public function setPrimaryKey(array $val)
    {
        $this->primaryKey = $val;
    }
    // --------------------------------------------------------------------

    /**
     * Get indexed fields field from our field list
     *
     * @return array field list
     */
    public function getIndexedFields()
    {
        $res = array();
        foreach ($this->fields as $k => $f) {
            if ($f->getIndex()) {
                $res[$k] = $f;
            }
        }

        return $res;
    }
    // --------------------------------------------------------------------
}
