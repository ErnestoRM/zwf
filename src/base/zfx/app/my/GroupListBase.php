<?php

/**
 * The GroupListBase class
 *
 * Utility class for group list management
 */
class GroupListBase
{

    /**
     * Change group
     * @param type $oldGroup
     * @param type $newGroup
     */
    public static function changeGroup($oldGroup, $newGroup)
    {
        $oldGroup = (int) $oldGroup;
        $newGroup = (int) $newGroup;
        $db = new \zfx\DB();
        $query = "
            UPDATE IGNORE   zfx_user_group
            SET             id_group = $newGroup
            WHERE           id_group = $oldGroup;

            DELETE FROM     zfx_user_group
            WHERE           id_group = $oldGroup;
        ";
        $db->setIgnoreErrors(TRUE);
        $db->qm($query);
    }
    // --------------------------------------------------------------------
}
