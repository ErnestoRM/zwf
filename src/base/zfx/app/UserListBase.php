<?php
namespace zfx;

/**
 * Class UserListBase
 *
 * Utility class for user list management
 *
 * @package zfx
 */
class UserListBase
{

    /**
     * Check if an email address is in use
     *
     * @return integer|NULL Numeric ID of user or NULL if not used
     */
    public static function getUserIDByEmail($email)
    {
        $db = new \zfx\DB();
        $email = $db->escape($email);
        $sql = "
            SELECT          id
            FROM            zfx_user
            WHERE           email = '$email'
        ";
        $id = $db->qr($sql, 'id');

        return ($id ? (int) $id : null);
    }
    // --------------------------------------------------------------------
}
