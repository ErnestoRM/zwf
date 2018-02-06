<?php
/* * ****************************************************************************
 *                                                                            *
 *  Copyright (c) 2017                                                        *
 *                                                                            *
 *  ActivexSoft S.L.                                                          *
 *                                                                            *
 *  Software desarrollado por Activexsoft.                                    *
 *  Todos los derechos reservados.                                            *
 *                                                                            *
 *                                                                            *
 *
 * @project      vsviajes                                                     *
 * @File         Model.php                                                    *
 * @Author       ernesto  ernesto@activexsoft.es                              *
 * @Date         :       8/16/2017                                            *
 *                                                                            *
 *                                                                            *
 * **************************************************************************** */
namespace zfx;

/**
 * Class Model
 *
 * @package zfx
 */
class Model extends DB
{

    /**
     * Nombre de la tabla del modelo de datos
     *
     * @var
     */
    public $table;

    /**
     * @var
     */
    public $schema;

    /**
     * Model constructor.
     *
     * @param      $table
     * @param null $profile
     *
     */
    public function __construct($table, $profile = null)
    {

        $this->table = $table;
        $this->schema = new \zfx\Schema();

        parent::__construct($profile);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Carga toods los datos del modelo dada la id
     *
     * @param $id
     *
     * @return bool
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function load($id)
    {

        $fields = $this->getByPKey($id);
        if (!$fields) {
            return false;
        }
        $this->schema->setFields($fields[0]);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve todos los registros dada la clave primaria
     *
     * @param $key
     *
     * @return array|bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getByPKey($key)
    {

        $pKey = $this->schema->getPKey();
        if (is_null($pKey)) {
            return false;
        }

        $query = "SELECT * FROM $this->table WHERE `{$pKey}` LIKE '$key'";

        return $this->qa($query);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción:Método vacío
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function setSchema()
    {

    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve todos los registros de una tabla, en el caso de recibir el parámetro sólo devuelve el
     * campo recibido de todos los registros
     *
     * @param null $field
     *
     * @return array|mixed|null
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getAll($field = null)
    {
        if ($field) {
            $query = "SELECT $field FROM $this->table";
        } else {
            $query = "SELECT * FROM $this->table";
        }

        return $this->qa($query);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve los registros cuyo campo especificado contiene el valor dado
     *
     * @param $field
     * @param $value
     *
     * @return array
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getBy($field, $value)
    {
        $query = "SELECT * FROM $this->table WHERE `{$field}` LIKE '$value'";

        return $this->qa($query);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Consulta los datos de la tabla que coincidan con el criterio del array dado por parámetro
     *
     * Formato array:
     *
     *      'campo'=>'valor'
     *
     * ADVERTENCIA: De momento sólo consulta la conjunción a falta de mejorar el método
     *
     * @param array $filters
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getByFilters(array $filters)
    {
        $query = "SELECT * FROM $this->table WHERE 1 ";

        foreach ($filters as $field => $value) {

            $query .= "and `{$field}` LIKE '{$value}' ";
        }
        echo $query;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción:
     *
     * @param $inserts
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function insert($inserts)
    {

        $fields = "(";
        $values = "(";

        foreach ($inserts as $field => $value) {
            $fields .= $field . ",";
            $values .= $value . ",";
        }
        $fields = trim($fields, ",");
        $values = trim($values, ",");

        $fields .= ")";
        $values .= ")";


        $query = "INSERT INTO `{$this->table}` $fields VALUES $values";

        echo $query;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Actualiza un campo con el valor dado para anular el registro
     *
     * @param $field
     * @param $value
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function softDeleteByPk($field, $value)
    {

        $this->updateFields([$field => $value]);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Actualiza todos los campos recibidos en el array.
     *
     * Estructura:
     *
     *              'campo'=>'nuevoValor'
     *
     * @param array $fields
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function updateFields(array $fields)
    {

        $pKey = $this->schema->getpKey();
        $valuePkey = $this->getField($pKey);

        $updates = "";

        foreach ($fields as $field => $value) {

            $field = $this->Escape($field);
            $value = $this->Escape($value);

            $updates .= "`{$field}`='{$value}',";
        }
        $updates = trim($updates, ',');

        $query = "UPDATE `{$this->table}` set $updates where $pKey like '$valuePkey'";

        return $this->q($query);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Devuelve el valor de un campo determinado siempre que se hayan cargado los datos
     *
     * @param $field
     *
     * @return bool|mixed
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getField($field)
    {

        $fields = $this->schema->getFields();

        if (va($fields)) {
            return $fields[$field];
        }

        return false;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Elimina el registro del objeto instanciado
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function deleteByPk()
    {
        $pKey = $this->schema->getpKey();
        $valuePkey = $this->getField($pKey);

        $query = "DELETE FROM `{$this->table}` WHERE  $pKey LIKE '$valuePkey'";

        echo $query;
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Actualiza el campo con el valor recibido
     *
     * @param $field
     * @param $value
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function updateField($field, $value)
    {

        $pKey = $this->schema->getpKey();
        $valuePkey = $this->getField($pKey);
        $field = $this->Escape($field);
        $value = $this->Escape($value);
        $query = "Update `{$this->table}` set `{$field}` = '$value' where $pKey like '$valuePkey'";

        return $this->q($query);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Hace una llamada al método del schema para simplificar la sintaxis de uso en el controlador
     *
     * @param $table
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function get($table)
    {
        return $this->schema->getRelated($table);
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Hace una llamada al método del schema para simplificar la sintaxis de uso en el controlador
     *
     * @return array
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function getValues()
    {
        return $this->schema->getFields();
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Cuenta los registros de la tabla. Si no se le pasan parámetros, los cuenta todos. Si se le pasa
     * la dupla campo valor, consulta los registros que cumplan esa condición.
     *
     * @param null $field
     * @param null $value
     *
     * @return mixed
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function count($field = null, $value = null)
    {

        if ($field == null or $value == null) {
            $query = "SELECT count(*) as count FROM `{$this->table}`";
        } else {

            $query = "SELECT count(`{$field}`) as count FROM `{$this->table}`  where `{$field}` LIKE '$value'";
        }

        return $this->qa($query)[0]['count'];
    }
    //--------------------------------------------------------------------------------------------------------------

    /**
     * Descripción: Busca una cadena dentro de la tabla. Sólo busca en los campos definidos como índices dentro del
     * modelo de datos.
     *
     * @param null $search
     *
     * @return array|mixed|null
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function search($search = null)
    {

        if ($search == null) {
            Controller::_redirect(Config::get('rootUrl'));
        }

        $fields = $this->schema->getIndexed();
        $pk = $this->schema->getPKey();
        $query = "Select * from `{$this->table}` where (";

        foreach ($fields as $field => $data) {
            if (\strtolower($data) == 'int') {
                //$query .= "`{$field}` = '{$search}' or ";
                $query .= " UPPER(`{$field}`) like UPPER('%$search%') or";
                //  $query .= " CONVERT(`{$field}` USING utf8) LIKE '%$search%' or";
                // $query.="UPPER(`{$field}`),";
            } else if (\strtolower($data) == 'string') {
                $query .= " UPPER(`{$field}`) like UPPER('%$search%') or";
                //$query .= " CONVERT(`{$field}` USING utf8) LIKE '%$search%' or";
            }
        }

        $query = \trim($query, "or");
        //$query .= ") like upper('%$search%')";

        $query .= ")order by cast( `$pk` as unsigned );";

        //echo $query;
        return $this->qa($query);
    }
}
