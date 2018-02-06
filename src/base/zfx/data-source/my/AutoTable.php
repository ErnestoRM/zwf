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
 * AutoTable for MySQL
 *
 * An AutoTable class builds itself from a given Scheme instance.
 */
class AutoTable extends AutoTableBase
{
    // --------------------------------------------------------------------

    /**
     * Read a set of records
     *
     * Returns a list of rows. An offset and limit can be specified.
     *
     * @param integer $offset
     * @param integet $limit
     * @param boolean $iterate Controls how the set of records will be returned:
     * If TRUE, return an interable object. If FALSE, returns a map.
     * @param string $key
     * @param string $pack Params for qa()
     */
    public function readRS($offset = 0, $limit = 0, $iterate = FALSE, $key = '', $pack = '')
    {
        if ($this->sqlList) {
            $offset = (int) $offset;
            $limit = (int) $limit;
            if ($limit > 0) {
                $limitSQL = "\nLIMIT $offset, $limit";
            } else {
                $limitSQL = '';
            }

            $filter = '';
            if ($this->sqlFilter) {
                $filter = "\nWHERE " . $this->sqlFilter . "\n";
            }

            $db = new DB($this->profile);
            if (!$iterate) {
                return $db->qa($this->sqlList . ' ' . $filter . ' ' . $this->sqlSortBy . ' ' . $limitSQL, $key, $pack);
            } else {
                $db->qs($this->sqlList . ' ' . $filter . ' ' . $this->sqlSortBy . ' ' . $limitSQL);
                return $db;
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Insert record
     *
     * Performs an INSERT command of a single row using given data.
     *
     * @param array $data RecordSet.
     * @param string $returnColumn Name of column to be returned
     *
     * @return mixed
     * If no data were provided it will return NULL.
     * If there were missing required fields returns an array of them.
     * FALSE on error.
     * On success and a return column were specified, it will return its value.
     * Otherwise it will return TRUE.
     */
    public function insertR($data, $returnColumn = NULL)
    {
        if (!$data) {
            return NULL;
        }
        $db = new DB($this->profile);
        $db->setIgnoreErrors(TRUE);
        $qtable = DB::quote($this->schema->getRelationName());
        $sql = "
            INSERT INTO $qtable
        ";
        $values = $this->genSQL_values($data);
        if (is_array($values)) {
            return $values;
        }
        if ($values) {
            $sql .= $values;
            if ($returnColumn) {
                if ($db->q($sql)) {
                    return $db->insert_id;
                } else {
                    return FALSE;
                }
            } else {
                return $db->q($sql);
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Self-initialize using the current Scheme
     */
    protected function createFromSchema()
    {
        // Exit on no fields
        if (!$this->schema->getFields())
            return;
        $db = new DB($this->profile);

        // Table name
        $this->qtable = $db->quote($this->schema->getRelationName());

        // Construct SQL query for listing
        $this->sqlList = "SELECT ";
        $numCols = $this->schema->count();
        $i = 0;
        foreach ($this->schema->getFields() as $column) {
            $this->sqlList .= $db->quote($column->getColumn());
            if ($i < $numCols - 1) {
                $this->sqlList .= ', ';
            }
            $i++;
        }
        $this->sqlList .= " FROM {$this->qtable}\n";


        // Construct SQL query for counting rows
        $rowcount = $db->quote('rowcount');
        $this->sqlCount = "SELECT COUNT(*) AS $rowcount FROM {$this->qtable}\n";

        // Construct SQL query for deletion
        $this->sqlDelete = "DELETE FROM {$this->qtable}\n";
    }
    // --------------------------------------------------------------------
}
