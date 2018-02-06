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
 *      @project      vsviajes                                                *
 *      @File        AppControllerBase.php                                    *
 *      @Author      ernesto  ernesto@activexsoft.es                          *
 *      @Date:       9/7/2017                                                 *
 *                                                                            *
 *                                                                            *
 * **************************************************************************** */
namespace zfx;

class AppControllerBase extends Controller
{

    /**
     * TRUE si estamos en una petición AJAX
     *
     * @var boolean $_ajax
     */
    protected $_ajax;

    /**
     * @var \zfx\UserBase del usuario activo $_user
     */
    private $_user = null;

    /**
     * Número de página actual
     *
     * @var integer $_numPage
     */
    private $_numPage = 0;

    // --------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        // Recuperamos la sesión
        $this->_setupLoggedUser();


        // Veamos si es una petición AJAX
        if (strtolower(\zfx\a($_SERVER, 'HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest') {
            $this->_ajax = true;
        } else {
            $this->_ajax = false;
        }


        // Procesamos los datos POST.
        $this->_procPOST();


        // Veamos si tenemos un formulario de login entrante.
        $this->_loginPOST();

        // Si no hay ningún usuario activo y es necesario mostramos la página de login
        if (!$this->_user && \zfx\Config::get('app-registration-required')) {
            \zfx\View::direct(\zfx\Config::get('app-login-form-view'));
            die;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Buscar la info del usuario en la sesión, si es que la había, y hacerla efectiva
     */
    private function _setupLoggedUser()
    {
        @session_start(array('cookie_lifetime' => 86400));
        $userId = \zfx\a($_SESSION, '_userId');
        if ($userId) {
            $this->_user = UserBase::get($userId);
        }
    }
    // --------------------------------------------------------------------

    /**
     * Si hay datos POST los almacenamos en la sesión y recargamos la
     * página para evitar solicitudes de reenvío de formulario en caso
     * de recarga.
     */
    protected function _procPOST()
    {
        if ($_POST) {
            $_SESSION['_post'] = $_POST;
            $this->_procFILES(); // Antes de redirect
            if (!$this->_ajax) {
                $this->_redirect($_SERVER['REQUEST_URI']);
            }
        } else {
            $this->_procFILES();
        }
    }
    // --------------------------------------------------------------------

    /**
     * Proceso de ficheros. De momento NOP
     */
    public function _procFILES()
    {

    }

    // --------------------------------------------------------------------

    protected function _loginPOST()
    {
        $post = \zfx\a($_SESSION, '_post');
        if (\zfx\av($post, 'name') && \zfx\av($post, 'password')) {
            unset($_SESSION['_post']);
            $user = UserBase::logName($post['name'], $post['password']);
            if ($user) {
                // Hacemos efectivo al usuario y salimos continuando con la página actual
                $this->_login($user);

                return true;
            } else {
                // Mostramos la vista de error y abortamos la ejecución
                \zfx\View::direct(\zfx\Config::get('app-login-error-view'));
                die;
            }
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Establecer un usuario como el usuario activo
     *
     * @param UserBase $user
     */
    public function _login(UserBase $user)
    {
        session_regenerate_id();
        $_SESSION['_userId'] = $user->getId();
        $this->_user = $user;
    }
    // --------------------------------------------------------------------

    /**
     * Obtener una instancia de un localizador (el del usuario o uno genérico)
     */
    public function _getLocalizer()
    {
        if ($this->_getUser()) {
            return new \zfx\Localizer($this->_getUser()->getLanguage());
        } else {
            return new \zfx\Localizer();
        }
    }
    // --------------------------------------------------------------------

    /**
     * Obtener el usuario activo
     *
     * @return User
     */
    public function _getUser()
    {
        return $this->_user;
    }
    // --------------------------------------------------------------------

    /**
     * Limpiar el usuario activo y destruir la sesión
     */
    public function _logout()
    {
        $_SESSION = array();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        session_destroy();
        $this->_user = null;
    }
    // --------------------------------------------------------------------

    /**
     * Obtener y establecer el número de página actual desde la URL
     *
     * Si el último segmento es un número de página activo, lo almacenamos y
     * apartamos de la lista de segmentos.
     *
     * Si $prefix no es nulo, se usará como prefijo para considerar el
     * último elemento como un número de página válido.
     * Ejemplo: $prefix es 'p' en http://miapp.com/clientes/p/3
     *
     * @param string $prefix Prefijo
     *
     * @return integer|null  Número de página o null si no se detectó
     */
    public function _processNumPage($prefix = null)
    {
        $usingPrefix = false;
        if ($prefix) {
            if ($this->_segmentCount() >= 2) {
                $prefixSegment = $this->_segment($this->_segmentCount() - 2);
                if ($prefixSegment !== $prefix) {
                    return null;
                }
            } else {
                return null;
            }
            $usingPrefix = true;
        }
        if ($this->_segmentCount() >= 1) {
            $lastSegment = $this->_segment($this->_segmentCount() - 1);
            if (\zfx\StrValidator::pageNumber($lastSegment)) {
                $this->_numPage = (int) $lastSegment;
                array_pop($this->_segments);
                if ($usingPrefix) {
                    array_pop($this->_segments);
                }

                return $this->_numPage;
            }
        }
    }
    // --------------------------------------------------------------------

    /**
     * Obtener el número de página actual.
     *
     * Usar después de _processNumPage().
     *
     * @see _processNumPage()
     *
     * @return integer|null Número de página actual o NULL
     */
    public function _getNumPage()
    {
        return $this->_numPage;
    }
    // --------------------------------------------------------------------
}
