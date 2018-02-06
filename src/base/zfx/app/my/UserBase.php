<?php
namespace zfx;

/**
 * Class UserBase
 *
 * Represents an application user that can sign up, log in and log out.
 *
 * @package zfx
 */
class UserBase extends Model
{

    /**
     * @var integer User numeric ID
     */
    private $id;

    /**
     * @var string User login name (AKA 'nick' or 'alias')
     */
    private $name;

    /**
     * @var string User email
     */
    private $email;

    /**
     *
     * @var string User ISO language code (2 letters)
     */
    private $language;

    /**
     * @var string Mobile phone number
     */
    private $mobile;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param integer $id       Numeric ID
     * @param string  $name     User name
     * @param string  $email    User email
     * @param string  $mobile   User mobile
     * @param string  $language User ISO language code (2 letters)
     */
    public function __construct($id, $name, $email, $mobile, $language)
    {


        $this->table = "zfx_user";
        $this->id = $id;
        $this->name = (string) $name;
        $this->email = (string) $email;
        $this->mobile = (string) $mobile;
        if (!$language || !in_array($language, \zfx\Config::get('languages'))) {
            $this->language = Config::get('defaultLanguage');
        } else {
            $this->language = $language;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Gets an existing user using login and password
     *
     * @param string $name     User name
     * @param type   $password User password
     *
     * @return UserBase|null User object reference or null if wrong name/password
     */
    public static function logName($name, $password)
    {
        $db = new \zfx\DB();

        $name = $db->escape($name);
        $hash = md5($password);

        $sql = "
            SELECT          *
            FROM            self::table
            WHERE           name          = '$name'
            AND             password_hash = '$hash';
        ";
        $userData = $db->qr($sql);
        if ($userData) {
            return new UserBase($userData['id'], $userData['name'], $userData['email'], $userData['language']);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Gets an existing user using email and password
     *
     * @param string $email    User email
     * @param type   $password User password
     *
     * @return UserBase|null User object reference or null if wrong email/password
     */
    public static function logEmail($email, $password)
    {
        $db = new \zfx\DB();

        $email = $db->escape($email);
        $hash = md5($password);

        $sql = "
            SELECT          *
            FROM            self::table
            WHERE           email         = '$email'
            AND             password_hash = '$hash';
        ";
        $userData = $db->qr($sql);
        if ($userData) {
            return new UserBase($userData['id'], $userData['name'], $userData['email'], $userData['language']);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Gets an existing user using ID
     *
     * @param integer $id
     *
     * @return UserBase|null User object reference or null if wrong ID
     */
    public static function get($id)
    {
        $db = new \zfx\DB();
        $id = (int) $id;

        $sql = "
            SELECT          *
            FROM            self::table
            WHERE           id = $id;
        ";
        $userData = $db->qr($sql);
        if ($userData) {
            return new UserBase($userData['id'], $userData['name'], $userData['email'], $userData['language']);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Create a new user in the system
     *
     * @param string $name     Desired nick
     * @param string $email    Email address
     * @param string $password Password (no hash)
     * @param string $language Language code
     *
     * @return UserBase Instance of created user or NULL on error
     */
    public static function create($name, $email, $password, $language = null)
    {
        $db = new \zfx\DB();

        $name = $db->escape($name);
        $email = $db->escape($email);
        $hash = md5($password);
        if (!$language || !in_array($language, Config::get('languages'))) {
            $language = Config::get('defaultLanguage');
        }


        $sql = "
            INSERT INTO     self::table
                            (name, email, password_hash, language)
            VALUES          ('$name', '$email', '$hash', '$language');
        ";
        $db->setIgnoreErrors(true);
        $res = $db->q($sql);
        if (!$res) {
            return null;
        }
        $id = (int) $db->insert_id;
        if ($id > 0) {

            return new UserBase($id, $name, $email, $language);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Delete from BD
     *
     * @return boolean TRUE on success
     */
    public function delete()
    {
        $db = new \zfx\DB();
        $sql = "
            DELETE FROM     self::table
            WHERE           id = {$this->id};
        ";

        return $db->q($sql);
    }
    // --------------------------------------------------------------------

    /**
     * Get user's numeric database ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    // --------------------------------------------------------------------

    /**
     * Get user's nickname
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    // --------------------------------------------------------------------

    /**
     * Set user's nickname
     *
     * @param type $val
     *
     * @return boolean
     */
    public function setName($val)
    {
        $res = $this->save('name', $val);
        if ($res) {
            $this->name = $val;

            return true;
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Save all user data
     *
     * @return boolean
     */
    private function save($column, $data)
    {
        $db = new \zfx\DB();
        $data = $db->escape($data);
        $sql = "
            UPDATE          self::table
            SET             $column = '$data'
            WHERE           id      = {$this->id};
        ";
        $db->setIgnoreErrors(true);

        return $db->q($sql);
    }
    // --------------------------------------------------------------------

    /**
     * Set user's password
     *
     * @param string $val
     *
     * @return bool
     */
    public function setPassword($val)
    {
        return $this->save('password_hash', md5($val));
    }
    // --------------------------------------------------------------------

    /**
     * Get user's email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    // --------------------------------------------------------------------

    /**
     * Set user's email
     *
     * @param string $val
     *
     * @return boolean TRUE on success
     */
    public function setEmail($val)
    {
        $res = $this->save('email', $val);
        if ($res) {
            $this->email = $val;

            return true;
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get user's language code
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
    // --------------------------------------------------------------------

    /**
     * Set user's language code
     *
     * @param string $val
     *
     * @return boolean TRUE on success
     */
    public function setLanguage($val)
    {
        if (!$val || !in_array($val, \zfx\Config::get('languages'))) {
            $val = \zfx\Config::get('defaultLanguage');
        }
        $res = $this->save('language', $val);
        if ($res) {
            $this->language = $val;

            return true;
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get user's mobile number
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }
    // --------------------------------------------------------------------

    /**
     * Get user's mobile number
     *
     * @param string $val
     *
     * @return boolean TRUE on success
     */
    public function setMobile($val = null)
    {
        $val = (string) $val;
        $res = $this->save('mobile', $val);
        if ($res) {
            $this->mobile = $val;

            return true;
        } else {
            return false;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Set user's arbitrary attribute
     *
     * @param string $code
     * @param string $value
     *
     * @return boolean TRUE on success
     */
    public function setAttr($code, $value)
    {
        $db = new \zfx\DB();
        $code = $db->escape($code);
        $value = $db->escape($value);
        $sql = "
            INSERT INTO zfx_userattribute SET
                id_user = {$this->id},
                code = '$code',
                value = '$value'
            ON DUPLICATE KEY UPDATE
                value = '$value';
        ";

        return $db->q($sql);
    }
    // --------------------------------------------------------------------

    /**
     * Get user's arbitrary attribute
     *
     * @param string $code
     *
     * @return string
     */
    public function getAttr($code)
    {
        $db = new \zfx\DB();
        $code = $db->escape($code);
        $sql = "
            SELECT          *
            FROM            zfx_userattribute
            WHERE           id_user = {$this->id}
            AND             code    = '$code';
        ";

        return $db->qr($sql);
    }
    // --------------------------------------------------------------------

    /**
     * Set user's arbitrary list of attributes
     *
     * @param array $attList Map of code=>values
     *
     * @return TRUE on success
     */
    public function setAttrs(array $attList)
    {
        $db = new \zfx\DB();
        $sql = '';
        if (va($attList)) {
            foreach ($attList as $k => $v) {
                $code = $db->escape($k);
                $value = $db->escape($v);
                $sql .= "
                    INSERT INTO zfx_userattribute SET
                        id_user = {$this->id},
                        code = '$code',
                        value = '$value'
                    ON DUPLICATE KEY UPDATE
                        value = '$value';
                ";
            }
        }

        return $db->qm($sql);
    }
    // --------------------------------------------------------------------

    /**
     * Get all user's arbitrary attributes
     *
     * @return array
     */
    public function getAttrs()
    {
        $db = new \zfx\DB();
        $sql = "
            SELECT          *
            FROM            zfx_userattribute
            WHERE           id_user = {$this->id};
        ";

        return $db->qa($sql, 'code');
    }
    // --------------------------------------------------------------------

    /**
     * Test if the user has a certain permission granted.
     *
     * @param string $code
     *
     * @return boolean TRUE if granted
     */
    public function hasPermission($code)
    {
        $db = new \zfx\BD();
        $code = $db->escape($code);
        $sql = "
            SELECT          zfx_permission.code
            FROM            zfx_permission, zfx_group_permission, zfx_user_group
            WHERE           zfx_permission.id = zfx_group_permission.id_permission
            AND             zfx_user_group.id_group = zfx_group_permission.id_group
            AND             zfx_user_group.id_user = {$this->id}
            AND             zfx_permission.code = '$code';
        ";

        return (bool) $db->qr($sql, 'code');
    }
    // --------------------------------------------------------------------

    /**
     * Get complete user's permission list
     *
     * @return array
     */
    public function getPermissions()
    {
        $db = new \zfx\DB();
        $sql = "
            SELECT          zfx_permission.code
            FROM            zfx_permission, zfx_group_permission, zfx_user_group
            WHERE           zfx_permission.id = zfx_group_permission.id_permission
            AND             zfx_user_group.id_group = zfx_group_permission.id_group
            AND             zfx_user_group.id_user = {$this->id}
        ";

        return array_keys($db->qa($sql, 'code'));
    }
    // --------------------------------------------------------------------

    /**
     * Clear and write a new set of groups
     *
     * @param array $groups
     */
    public function setAllGroups($groups = null)
    {
        $db = new \zfx\DB();
        $sql = "DELETE FROM zfx_user_group WHERE id_user={$this->id}";
        $db->q($sql);
        $num = count($groups);
        if ($num > 0) {
            $values = '';
            $i = 1;
            foreach ($groups as $p) {
                $values .= '(' . $this->id . ',' . (int) $p . ')';
                $i++;
                if ($i <= $num) {
                    $values .= ',';
                }
            }
            $sql = "
                INSERT INTO         zfx_user_group
                                    (
                                        id_user,
                                        id_group
                                    )
                VALUES              $values;
            ";
            $db->setIgnoreErrors(true);
            $db->q($sql);
        }
    }
    // --------------------------------------------------------------------
}
