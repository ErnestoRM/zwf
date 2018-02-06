<?php
namespace zfx;

/**
 * Class GroupBase
 *
 * Represents an group of users
 *
 * @package zfx
 */
class GroupBase
{

    /**
     * @var integer Group numeric ID
     */
    protected $id;

    /**
     * @var string $name Group name
     */
    protected $name;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param integer $id Numeric ID of the group
     * @param string  $name
     */
    protected function __construct($id, $name)
    {
        $this->id = (int) $id;
        $this->name = (string) $name;
    }
    // --------------------------------------------------------------------

    /**
     * Gets an existing group using ID
     *
     * @param integer $id
     *
     * @return \zfx\GroupBase|null Group object reference or null if wrong ID
     */
    public static function get($id)
    {
        $db = new \zfx\DB();
        $id = (int) $id;

        $sql = "
            SELECT          *
            FROM            zfx_group
            WHERE           id = $id;
        ";
        $data = $db->qr($sql);
        if ($data) {
            return new GroupBase($data['id'], $data['name']);
        } else {
            return null;
        }
    }
    // --------------------------------------------------------------------

    /**
     * Get group numeric database ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    // --------------------------------------------------------------------

    /**
     * Get group nickname
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    // --------------------------------------------------------------------

    /**
     * Clear and write a new set of permissions
     *
     * @param array $permissions
     */
    public function setAllPermissions($permissions = null)
    {
        $db = new \zfx\DB();
        $sql = "DELETE FROM zfx_group_permission WHERE id_group={$this->id}";
        $db->q($sql);
        $num = count($permissions);
        if ($num > 0) {
            $values = '';
            $i = 1;
            foreach ($permissions as $p) {
                $values .= '(' . $this->id . ',' . (int) $p . ')';
                $i++;
                if ($i <= $num) {
                    $values .= ',';
                }
            }
            $sql = "
                INSERT INTO         zfx_group_permission
                                    (
                                        id_group,
                                        id_permission
                                    )
                VALUES              $values;
            ";
            $db->setIgnoreErrors(true);
            $db->q($sql);
        }
    }
    // --------------------------------------------------------------------
}
