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
 * AutoSchema for PostgreSQL
 *
 * Schema that can build itself from database catalog.
 */
class AutoSchema extends AutoSchemaBase
{

    /**
     * @var string $tableOid
     */
    private $tableOid;

    // --------------------------------------------------------------------

    /**
     * Automatic self construction
     *
     * Initializes object and builds from the system catalog by loading
     * the table definition.
     */
    protected function autoConstruct()
    {
        // Get column names
        $db = new DB($this->profile);
        $etable = $db->escape($this->getRelationName());

        $dbconf = a(Config::get('pg'), $this->profile);
        if (va($dbconf) && array_key_exists('dbNamespace', $dbconf)) {
            $nsp = $db->escape($dbconf['dbNamespace']);
            $cond = "AND pg_class.relnamespace = (select pg_namespace.oid from pg_namespace where pg_namespace.nspname = '$nsp')";
        } else {
            $cond = '';
        }

        $columns = $db->qa("
                SELECT      pg_attribute.*,
                            format_type(pg_attribute.atttypid, pg_attribute.atttypmod) AS \"sql_type\",
                            pg_class.oid AS \"table_oid\",
                            pg_class.relnamespace
                FROM        pg_attribute, pg_class
                WHERE       pg_class.relname = '$etable'
                AND         pg_class.oid = pg_attribute.attrelid
                $cond
                AND         pg_attribute.attisdropped = FALSE
                AND         pg_attribute.attnum > 0
            ", 'attnum');

        if ($columns) {
            $this->tableOid = (int) a(current($columns), 'table_oid');

            // Get fields
            foreach ($columns as $field) {
                $this->setField($field['attname'], $this->createField($field));
            }

            // Get primary keys and indexes
            $pk = array();
            $ind = $db->qa("
                    SELECT      pg_index.*
                    FROM        pg_index
                    WHERE       pg_index.indrelid = {$this->tableOid};
                ");
            if ($ind) {
                foreach ($ind as $i) {
                    $keysArray = explode(' ', $i['indkey']);
                    foreach ($keysArray as $key) {
                        if ($key > 0) {
                            $this->setIndex(a(a($columns, $key), 'attname'));
                            // Primary key? Add to PK list
                            if ($i['indisprimary'] == 't') {
                                $pk[] = a(a($columns, $key), 'attname');
                            }
                        }
                    }
                }
            }
            $this->setPrimaryKey($pk);

            // Get foreign keys
            $foreignKeys = $db->qa("
                    SELECT      pg_constraint.*,
                                pg_class.relname,
                                array(SELECT attname FROM pg_attribute WHERE pg_attribute.attrelid = pg_constraint.conrelid  AND pg_attribute.attnum = ANY (pg_constraint.conkey)) AS \"lcols\",
                                array(SELECT attname FROM pg_attribute WHERE pg_attribute.attrelid = pg_constraint.confrelid  AND pg_attribute.attnum = ANY (pg_constraint.confkey)) AS \"fcols\"
                    FROM        pg_constraint, pg_class
                    WHERE       pg_constraint.conrelid = {$this->tableOid}
                    AND         pg_constraint.contype = 'f'
                    AND         pg_class.oid = pg_constraint.confrelid
                ");

            if ($foreignKeys) {
                $fks = array();
                foreach ($foreignKeys as $fk) {
                    $constraintID = $fk['conname'];
                    if (!a($fks, $constraintID)) {
                        $fks[$constraintID] = new ForeignKey();
                    }
                    $fks[$constraintID]->setName($constraintID);
                    $fks[$constraintID]->setRelation($fk['relname']);
                    $fks[$constraintID]->setLocalColumns(DB::parseArray($fk['lcols']));
                    $fks[$constraintID]->setForeignColumns(DB::parseArray($fk['fcols']));
                }
                $this->setFks($fks);
            }
        } else {
            throw new \Exception;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Create Field from system catalog info
     *
     * Creates a child of \zfx\Field object using provided info from system
     * catalog.
     *
     * @param array $fieldInfo Info data from pg_attribute table
     * @return null | \zfx\Field
     */
    private function createField(array $fieldInfo)
    {
        $res = NULL;
        preg_match('/^([a-z ]+)(?:\((.+?)\))?/u', $fieldInfo['sql_type'], $res);
        switch (a($res, 1)) {
            case 'smallint': {
                    $f = new FieldInt();
                    $f->setLowerLimit(-32768);
                    $f->setUpperLimit(32767);
                    break;
                }
            case 'integer': {
                    $f = new FieldInt();
                    $f->setLowerLimit(-2147483648);
                    $f->setUpperLimit(2147483647);
                    break;
                }
            case 'bigint': {
                    $f = new FieldInt();
                    $f->setLowerLimit(-9223372036854775808);
                    $f->setUpperLimit(9223372036854775807);
                    break;
                }
            case 'boolean': {
                    $f = new FieldBoolean();
                    break;
                }
            case 'character varying':
            case 'character': {
                    $f = new FieldString();
                    $f->setMax((int) a($res, 2));
                    break;
                }
            case 'double precision': {
                    $f = new FieldReal();
                    break;
                }
            case 'numeric': {
                    $f = new FieldReal();
                    break;
                }
            case 'real': {
                    $f = new FieldReal();
                    break;
                }
            case 'text': {
                    $f = new FieldString();
                    $f->setMax(-1);
                    break;
                }
            case 'date': {
                    $f = new FieldDate();
                    break;
                }
            case 'time':
            case 'time without time zone':
            case 'time with time zone': {
                    $f = new FieldTime();
                    break;
                }
            case 'timestamp':
            case 'timestamp without time zone':
            case 'timestamp with time zone': {
                    $f = new FieldDateTime();
                    break;
                }
            default: {
                    return NULL;
                    break;
                }
        }

        // The ID will be the column name
        $f->setColumn($fieldInfo['attname']);

        // Columns with a defined default value generates fields with not-required attribute set
        if ($fieldInfo['atthasdef'] == 't') {
            $f->setAuto(TRUE);
        }

        // Is NULL allowed?
        if ($fieldInfo['attnotnull'] == 't') {
            $f->setRequired(TRUE);
        }

        return $f;
    }
    // --------------------------------------------------------------------

    /**
     * Get inverse foreign keys
     *
     * Searches system catalog for tables that have foreign keys pointing to us.
     *
     * @return array List of \zfx\ForeignKey
     */
    public function calculateRelTables()
    {
        if ($this->tableOid === NULL) {
            return;
        }
        $db = new DB($this->profile);
        $rels = $db->qa("
                    SELECT      pg_constraint.*,
                                pg_class.relname,
                                array(SELECT attname FROM pg_attribute WHERE pg_attribute.attrelid = pg_constraint.conrelid  AND pg_attribute.attnum = ANY (pg_constraint.conkey)) AS \"lcols\",
                                array(SELECT attname FROM pg_attribute WHERE pg_attribute.attrelid = pg_constraint.confrelid  AND pg_attribute.attnum = ANY (pg_constraint.confkey)) AS \"fcols\"
                    FROM        pg_constraint, pg_class
                    WHERE       pg_constraint.confrelid = $this->tableOid
                    AND         pg_constraint.contype = 'f'
                    AND         pg_class.oid = pg_constraint.conrelid
                ");

        $fks = array();
        if ($rels) {
            foreach ($rels as $rel) {
                $fk = new ForeignKey();
                $fk->setRelation($rel['relname']);
                $fk->setName($rel['conname']);
                $fk->setLocalColumns(DB::parseArray($rel['lcols']));
                $fk->setForeignColumns(DB::parseArray($rel['fcols']));
                $fks[$rel['relname']] = $fk;
            }
        }
        $this->setRelTables($fks);
    }
    // --------------------------------------------------------------------
}
