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
 * AutoSchema for MySQL
 *
 * Schema that can build itself from database catalog.
 */
class AutoSchema extends AutoSchemaBase
{
    // --------------------------------------------------------------------

    /**
     * Automatic self construction
     *
     * Initializes object and builds from the system catalog by loading
     * the table definition.
     */
    protected function autoConstruct()
    {
        // Obtener los nombres de columna
        $db = new DB($this->profile);
        $etable = $db->escape($this->getRelationName());

        // Obtener campos desde el catálogo de la base de datos
        $database = $db->escape(aa(Config::get('my'), $this->profile, 'dbDatabase'));

        $columns = $db->qa("
                SELECT      *
                FROM        information_schema.COLUMNS
                WHERE       table_name = '$etable'
                AND         table_schema = '$database'
            ");
        if ($columns) {
            // Obtener nombres y claves
            $pk = array();
            foreach ($columns as $field) {

                // Add Field
                $this->setField($field['COLUMN_NAME'], $this->createField($field));

                // Si además era un índice, lo puedo asignar desde aquí mismo
                if ($field['COLUMN_KEY'] !== '') {
                    $this->setIndex($field['COLUMN_NAME']);
                }

                // Si además era una clave primaria, lo guardo aquí.
                if ($field['COLUMN_KEY'] === 'PRI') {
                    $pk[] = $field['COLUMN_NAME'];
                }
            }
            $this->setPrimaryKey($pk);

            // Si no hay campos, salimos.
            if (!$this->getFields())
                return;


            // Obtener referencias foráneas
            $fks = $db->qa("
                SELECT      *
                FROM        information_schema.KEY_COLUMN_USAGE
                WHERE       TABLE_SCHEMA = '$database'
                AND         TABLE_NAME = '$etable'
                AND         REFERENCED_TABLE_NAME IS NOT NULL
            ");
            $foreignKeys = array();
            if ($fks) {
                foreach ($fks as $fk) {
                    $constraintID = $fk['CONSTRAINT_NAME'];
                    if (!a($foreignKeys, $constraintID)) {
                        $foreignKeys[$constraintID] = new ForeignKey();
                        $foreignKeys[$constraintID]->setName($constraintID);
                        $foreignKeys[$constraintID]->setRelation($fk['REFERENCED_TABLE_NAME']);
                    }
                    $foreignKeys[$constraintID]->addLocalColumn($fk['COLUMN_NAME']);
                    $foreignKeys[$constraintID]->addForeignColumn($fk['REFERENCED_COLUMN_NAME']);
                }
                $this->setFks($foreignKeys);
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
    private function createField($fieldInfo)
    {
        $res = NULL;
        preg_match('/^([a-z]+)(?:\((.+?)\))?(?: (unsigned))?/u', $fieldInfo['COLUMN_TYPE'], $res);
        switch ($res[1]) {
            case 'tinyint': {
                    if (a($res, 2) == '1') {
                        // Es boolean
                        $f = new FieldBoolean();
                        break;
                    } else {
                        // Es tinyint
                        $f = new FieldInt();
                        if (a($res, 3)) {
                            $f->setLowerLimit(0);
                            $f->setUpperLimit(255);
                        } else {
                            $f->setLowerLimit(-128);
                            $f->setUpperLimit(127);
                        }
                    }
                    break;
                }
            case 'smallint': {
                    $f = new FieldInt();
                    if (a($res, 3)) {
                        $f->setLowerLimit(0);
                        $f->setUpperLimit(65535);
                    } else {
                        $f->setLowerLimit(-32768);
                        $f->setUpperLimit(32767);
                    }
                    break;
                }
            case 'mediumint': {
                    $f = new FieldInt();
                    if (a($res, 3)) {
                        $f->setLowerLimit(0);
                        $f->setUpperLimit(16777215);
                    } else {
                        $f->setLowerLimit(-8388608);
                        $f->setUpperLimit(8388607);
                    }
                    break;
                }
            case 'int': {
                    $f = new FieldInt();
                    if (a($res, 3)) {
                        $f->setLowerLimit(0);
                        $f->setUpperLimit(4294967295);
                    } else {
                        $f->setLowerLimit(-2147483648);
                        $f->setUpperLimit(2147483647);
                    }
                    break;
                }
            case 'bigint': {
                    $f = new FieldInt();
                    if (a($res, 3)) {
                        $f->setLowerLimit(0);
                        $f->setUpperLimit(18446744073709551615);
                    } else {
                        $f->setLowerLimit(-9223372036854775808);
                        $f->setUpperLimit(9223372036854775807);
                    }
                    break;
                }
            case 'varchar':
            case 'char': {
                    $f = new FieldString();
                    $f->setMax((int) $fieldInfo['CHARACTER_MAXIMUM_LENGTH']);
                    break;
                }
            case 'decimal':
            case 'float':
            case 'double': {
                    $f = new FieldReal();
                    break;
                }
            case 'text':
            case 'tinytext':
            case 'mediumtext':
            case 'longtext': {
                    $f = new FieldString();
                    $f->setMax(-1);
                    break;
                }
            case 'time': {
                    $f = new FieldTime();
                    break;
                }
            case 'date': {
                    $f = new FieldDate();
                    break;
                }
            case 'datetime':
            case 'timestamp': {
                    $f = new FieldDateTime();
                    break;
                }
            default: {
                    return NULL;
                    break;
                }
        }


        // De momento el identificador será el propio nombre de la columna
        $f->setColumn($fieldInfo['COLUMN_NAME']);

        // Si tiene valor por defecto
        if ($fieldInfo['COLUMN_DEFAULT'] != '' || $fieldInfo['EXTRA'] == 'auto_increment') {
            $f->setAuto(TRUE);
        }

        // ¿Puede ser NULL?
        if ($fieldInfo['IS_NULLABLE'] == 'NO') {
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
        // Obtener referencias foráneas inversas
        $db = new DB($this->profile);
        $etable = $db->escape($this->getRelationName());
        $database = $db->escape(a(a(Config::get('my'), $this->profile), 'dbDatabase'));
        $db = new DB($this->profile);
        $rels = $db->qa("
            SELECT      *
            FROM        information_schema.KEY_COLUMN_USAGE
            WHERE       REFERENCED_TABLE_SCHEMA = '$database'
            AND         REFERENCED_TABLE_NAME = '$etable'
        ");
        $fks = array();
        if ($rels) {
            foreach ($rels as $fk) {
                if (!isset($fks[$fk['TABLE_NAME']])) {
                    $fks[$fk['TABLE_NAME']] = new ForeignKey();
                    $fks[$fk['TABLE_NAME']]->setRelation($fk['TABLE_NAME']);
                    $fks[$fk['TABLE_NAME']]->setName($fk['CONSTRAINT_NAME']);
                }
                $fks[$fk['TABLE_NAME']]->addLocalColumn($fk['COLUMN_NAME']);
                $fks[$fk['TABLE_NAME']]->addForeignColumn($fk['REFERENCED_COLUMN_NAME']);
            }
        }
        $this->setRelTables($fks);
    }
    // --------------------------------------------------------------------
}
