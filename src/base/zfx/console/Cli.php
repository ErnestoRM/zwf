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
 * @File         Cli.php                                                      *
 * @Author       ernesto  ernesto@activexsoft.es                              *
 * @Date         :       9/9/2017                                             *
 *                                                                            *
 *                                                                            *
 * **************************************************************************** */
namespace zfx;

require('base/zfx/core/Config.php');
Config::setup();
require('base/zfx/core/core.php');

/**
 * Class Console
 */
class Cli
{

    /**
     * @var array
     */
    private $args = array();

    /**
     * Console constructor.
     * * @param $args
     *
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Descripción: Método que laza la app
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    public function run()
    {
        $this->_autoexec();
    }

    /**
     * Descripción: Método que detecta la función que se quiere ejecutar
     *
     *
     * @return mixed
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function _autoexec()
    {

        if (count($this->args) == 1) {
            echo "Me faltan argumentos... ;)\n";
            $this->stop(0);
        } else {
            $method = $this->args[1];

            if (method_exists($this, $this->args[1])) {
                return $this->$method();
            } else {
                echo $this->args[1] . " No existe\n";
                $this->stop(0);
            }
        }
    }

    /**
     * Descripción:
     *
     * @param $status
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function stop($status)
    {
        exit($status);
    }

    /**
     * Descripción: Método de prueba
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function saluda()
    {

        echo "hola, ¿cómo estás?\n";
        $this->stop(1);
    }

    /**
     * Descripción: Método encargado de crear elementos del  M-V-C de la aplicación
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function make()
    {

        if (count($this->args) == 3) {
            echo "Falta el nombre del " . $this->args[2] . "\n";
            $this->stop(0);
        } elseif (count($this->args) == 2) {
            echo "make ¿qué más?\n";
        }

        switch ($this->args[2]) {

            case 'controller':
                $this->makeController();
                break;
            case 'view':
                $this->makeView();
                break;
            case 'model':
                $this->makeModel();
                break;
            case 'module':

                $this->makeController();
                $this->makeView();
                $this->makeModel();
                break;
        }
    }

    /**
     * Descripción: Método encargado de crear un nuevo controlador
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function makeController()
    {

        $controllerName = ucwords(strtolower($this->args[3]));

        $lowerModelName = (strtolower($this->args[3]));
        $modelName = Config::get('modelPrefix') . ucwords($lowerModelName);

        $varname = "\$m" . ucwords($lowerModelName);
        $call = $varname . "->getAll()";

        $pathToView = strtolower($this->args[3]);

        $file = Config::get('controllerPath') . $controllerName . ".php";

        if ($this->exist($file)) {
            echo $file . " already exists\n";
        } else {
            $content = "<?php

class $controllerName extends Zerfrex
{

    public function _main()
    {

        \$var['name']='$file';
        $varname = new $modelName();
       /* \$all = $call;
        \$var['all'] = \$all; */

        zfx\View::direct( '$pathToView/index',\$var);

    }

	//Happy coding!
   // --------------------------------------------------------------------
}";
            $this->create($file, $content);
        }
    }

    /**
     * Descripción: Método que determina si existe un determinado elemento
     *
     * @param $file
     *
     * @return bool
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function exist($file)
    {
        return file_exists($file);
    }

    /**
     * Descripción: Método encargado de crear un elemento nuevo
     *
     * @param $file
     * @param $content
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function create($file, $content)
    {
        if (file_put_contents($file, $content)) {
            echo $file . " was succesfully created\n";
        } else {
            echo "A problem occurred while creating models/" . $file . "\n";
        }
    }

    /**
     * Descripción: Método encargado de crear una nueva vista
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function makeView()
    {

        if (count($this->args) == 5) {
            $viewName = ucwords(strtolower($this->args[4]));
        } else {
            $viewName = "Index";
        }

        $dir = Config::get('viewPath') . strtolower($this->args[3]);
        $file = $dir . DIRECTORY_SEPARATOR . $viewName . ".php";


        if ($this->exist($file)) {
            echo $file . " already exists\n";
        } else {
            $content = file_get_contents("views/index.php");

            if (!$this->exist($dir)) {
                mkdir($dir);
            }
            $this->create($file, $content);
        }
    }

    /**
     * Descripción: Método encargado de crear un nuevo modelo
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function makeModel()
    {

        $lowerModelName = (strtolower($this->args[3]));
        $modelName = Config::get('modelPrefix') . ucwords($lowerModelName);


        $file = Config::get('modelPath') . Config::get('dbSys') . DIRECTORY_SEPARATOR . $modelName . '.php';


        if ($this->exist($file)) {
            echo $file . " already exists\n";
        } else {
            $content = "<?php


    /**
     * Class $modelName
     */
    class $modelName extends \zfx\Model
    {
        /**
         *
         */
        const TABLE = '$lowerModelName';

        /**
         * $modelName constructor.
         * @param null \$id
         *
         * @param null \$profile
         *
         */
        public function __construct(\$id = null, \$profile = null)
        {

            parent::__construct(self::TABLE, \$profile);
            \$this->setSchema();

            if (\$id != null) {
                \$this->load(\$id);

            }
        }
        //--------------------------------------------------------------------------------------------------------------

        /**
         * Descripción: Define el esquema de este modelo de datos
         *
         */
        public function setSchema()
        {
            //TODO definir el modelo de datos para $modelName


            \$this->schema->setFields(\$fields = [
                'ID' => ['index' => true, 'type' => 'int', 'length' => '', 'value' => ''],

            ]);

            \$this->schema->setPKey('ID');

        }
        //--------------------------------------------------------------------------------------------------------------

        //Happy coding!

    }";
            $this->create($file, $content);
        }
    }

    /**
     * Descripción: Método que se encarga de eliminar elementos M-V-C de la aplicación
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function delete()
    {


        if (count($this->args) == 3) {
            echo "Falta el nombre del " . $this->args[2] . "\n";
            $this->stop(0);
        } elseif (count($this->args) == 2) {
            echo "delete ¿qué más?\n";
        }

        switch ($this->args[2]) {
            case 'controller':
                $this->deleteController();
                break;
            case 'view':
                $this->deleteView();

                break;
            case 'model':
                $this->deleteModel();
                break;
            case 'module':
                $this->deleteController();
                $this->deleteView(true);
                $this->deleteModel();
                break;
        }
    }

    /**
     * Descripción: Método que elimina un controlador
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function deleteController()
    {
        $controllerName = ucwords(strtolower($this->args[3]));

        $file = Config::get('controllerPath') . $controllerName . ".php";
        $this->deleteFile($file);
    }

    /**
     * Descripción: Método que elimina un fichero
     *
     * @param $file
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function deleteFile($file)
    {
        if ($this->exist($file)) {
            if (unlink($file)) {
                echo $file . " was succesfully deleted\n";
            } else {
                echo "A problem occurred while deleting " . $file . "\n";
            }
        } else {
            echo $file . " file doesn't exist\n";
        }
    }

    /**
     * Descripción: Método que elimina una vista.
     * Si recibe un TRUE como parámetro, elimina todos los elementos de la vista sin preguntar nada antes.
     * Si no recibe parámetro o recibe un FALSE, lista todas las vistas disponibles y permite elegir qué vista
     * eliminar.
     *
     * @param null $option
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function deleteView($option = null)
    {

        $dir = strtolower($this->args[3]);
        $completeDir = Config::get('viewPath') . $dir;
        if (@is_dir($completeDir)) {
            // $fileData = $this->dirToMultiArray( new \DirectoryIterator( $dir ) );
            $fileData = $this->dirToArray(new \DirectoryIterator($completeDir));


            if (!$option) {
                echo "[0]--ELIMINAR TODO EL DIRECTORIO " . $dir . DIRECTORY_SEPARATOR . "\n";
                echo "[1]--CANCELAR\n";

                foreach ($fileData as $key => $value) {

                    echo "[" . ($key + 2) . "]--" . $dir . DIRECTORY_SEPARATOR . $value . "\n";
                }
                $option = readline("Elija la vista que quiere eliminar\n");


                if ($option > count($fileData) + 1) {

                    $option = readline("Esa vista no existe. Elija la vista que quiere eliminar\n");
                }


                switch ($option) {
                    case 0:
                        $toDelete = $completeDir;
                        break;
                    case 1:
                        echo "Proceso cancelado\n";
                        $this->stop(0);
                        break;
                    default:
                        $toDelete = $completeDir . DIRECTORY_SEPARATOR . $fileData[($option - 2)];
                        break;
                }
            } else {
                $toDelete = $completeDir;
            }

            if (@is_dir($toDelete)) {
                $this->deleteDir($toDelete);
            } else {
                $this->deleteFile($toDelete);
            }
        } else {

            echo "no existen vistas\n";
        }
    }

    /**
     * Descripción: Método que convierte un arbol de directorios en un array asociativo
     *
     * @param \DirectoryIterator $dir
     *
     * @return array
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function dirToArray(\DirectoryIterator $dir)
    {
        $data = array();
        $auxData = array();
        foreach ($dir as $node) {

            if ($node->isDir() && !$node->isDot()) {

                $auxData[$node->getFilename()] = $this->dirToArray(new \DirectoryIterator($node->getPathname()));

                foreach ($auxData as $dir => $files) {
                    $data[] = $dir . DIRECTORY_SEPARATOR;
                    foreach ($files as $file) {
                        $data[] = $dir . DIRECTORY_SEPARATOR . $file;
                    }
                }

                // $data[$node->getFilename()] = $this->dirToArray( new \DirectoryIterator( $node->getPathname() ) );
            } else {
                if ($node->isFile()) {
                    $data[] = $node->getFilename();
                }
            }
        }

        return $data;
    }

    /**
     * Descripción: Método que elimina un directorio y to-do su contenido recusrivamente
     *
     * @param $dir
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function deleteDir($dir)
    {

        foreach (glob($dir . "/*") as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                $this->deleteFile($file);
            }
        }
        rmdir($dir);
    }

    /**
     * Descripción: Método que elimina un modelo
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function deleteModel()
    {

        $lowerModelName = (strtolower($this->args[3]));
        $modelName = "M" . ucwords($lowerModelName);
        $file = Config::get('modelPath') . Config::get('dbSys') . DIRECTORY_SEPARATOR . $modelName . '.php';
        $this->deleteFile($file);
    }

    /**
     * Descripción: Método que convierte un árbol de directorios en un array multidimensional
     *
     * @param \DirectoryIterator $dir
     *
     * @return array
     *
     *
     * @author Ernesto Roselló  ernesto@activexsoft.es
     */
    private function dirToMultiArray(\DirectoryIterator $dir)
    {
        $data = array();
        foreach ($dir as $node) {
            if ($node->isDir() && !$node->isDot()) {
                $data[$node->getFilename()] = $this->dirToArray(new \DirectoryIterator($node->getPathname()));
            } else {
                if ($node->isFile()) {
                    $data[] = $node->getFilename();
                }
            }
        }

        return $data;
    }
}
