<?php
/* Copyright (C) 2025 Your Name <your@email.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file       class/agendalimitgroup.class.php
 * \ingroup    agendalimit
 * \brief      Class to manage agenda limits for user groups
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class AgendaLimitGroup
 * Manages agenda lookahead limits for user groups
 */
class AgendaLimitGroup extends CommonObject
{
    /**
     * @var string ID of module
     */
    public $module = 'agendalimit';

    /**
     * @var string Element type
     */
    public $element = 'agendalimitgroup';

    /**
     * @var string Name of table without prefix
     */
    public $table_element = 'agendalimit_group';

    /**
     * @var int User Group ID
     */
    public $fk_usergroup;

    /**
     * @var int Whether limit is enabled (1) or disabled (0)
     */
    public $limit_enabled;

    /**
     * @var int Number of days users in this group can look ahead
     */
    public $limit_days;

    /**
     * @var int User who created record
     */
    public $fk_user_creat;

    /**
     * @var int User who last modified record
     */
    public $fk_user_modif;

    /**
     * @var string Date of creation
     */
    public $date_creation;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        global $conf, $langs;

        $this->db = $db;
    }

    /**
     * Create object into database
     *
     * @param User $user User that creates
     * @param bool $notrigger false=launch triggers after, true=disable triggers
     * @return int <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false)
    {
        global $conf;

        $error = 0;

        // Clean parameters
        if (isset($this->fk_usergroup)) {
            $this->fk_usergroup = (int) $this->fk_usergroup;
        }
        if (isset($this->limit_enabled)) {
            $this->limit_enabled = (int) $this->limit_enabled;
        }
        if (isset($this->limit_days)) {
            $this->limit_days = (int) $this->limit_days;
        }

        // Check parameters
        if (empty($this->fk_usergroup)) {
            $this->error = 'ErrorUserGroupRequired';
            return -1;
        }

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        $sql .= "fk_usergroup,";
        $sql .= "limit_enabled,";
        $sql .= "limit_days,";
        $sql .= "date_creation,";
        $sql .= "fk_user_creat";
        $sql .= ") VALUES (";
        $sql .= ((int) $this->fk_usergroup).",";
        $sql .= ((int) $this->limit_enabled).",";
        $sql .= ((int) $this->limit_days).",";
        $sql .= "'".$this->db->idate(dol_now())."',";
        $sql .= ((int) $user->id);
        $sql .= ")";

        dol_syslog(get_class($this)."::create", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
        }

        if (!$error) {
            $this->db->commit();
            return $this->id;
        } else {
            $this->db->rollback();
            return -1 * $error;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id            Id of group limit record
     * @param int    $fk_usergroup  User Group ID to load limit for
     * @return int                  <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id = 0, $fk_usergroup = 0)
    {
        $sql = "SELECT rowid, fk_usergroup, limit_enabled, limit_days, date_creation, fk_user_creat, fk_user_modif";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        if ($id > 0) {
            $sql .= " WHERE rowid = ".((int) $id);
        } elseif ($fk_usergroup > 0) {
            $sql .= " WHERE fk_usergroup = ".((int) $fk_usergroup);
        } else {
            return -1;
        }

        dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;
                $this->fk_usergroup = $obj->fk_usergroup;
                $this->limit_enabled = $obj->limit_enabled;
                $this->limit_days = $obj->limit_days;
                $this->date_creation = $this->db->jdate($obj->date_creation);
                $this->fk_user_creat = $obj->fk_user_creat;
                $this->fk_user_modif = $obj->fk_user_modif;

                $this->db->free($resql);
                return 1;
            } else {
                $this->db->free($resql);
                return 0;
            }
        } else {
            $this->error = "Error ".$this->db->lasterror();
            return -1;
        }
    }

    /**
     * Update object into database
     *
     * @param User $user User that modifies
     * @param bool $notrigger false=launch triggers after, true=disable triggers
     * @return int <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
        global $conf;

        $error = 0;

        // Clean parameters
        if (isset($this->limit_enabled)) {
            $this->limit_enabled = (int) $this->limit_enabled;
        }
        if (isset($this->limit_days)) {
            $this->limit_days = (int) $this->limit_days;
        }

        $this->db->begin();

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        $sql .= " limit_enabled = ".((int) $this->limit_enabled).",";
        $sql .= " limit_days = ".((int) $this->limit_days).",";
        $sql .= " fk_user_modif = ".((int) $user->id);
        $sql .= " WHERE rowid = ".((int) $this->id);

        dol_syslog(get_class($this)."::update", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1 * $error;
        }
    }

    /**
     * Delete object in database
     *
     * @param User $user User that deletes
     * @param bool $notrigger false=launch triggers after, true=disable triggers
     * @return int <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        $error = 0;

        $this->db->begin();

        $sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE rowid = ".((int) $this->id);

        dol_syslog(get_class($this)."::delete", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1 * $error;
        }
    }

    /**
     * Get all group limits
     *
     * @return array Array of group limit records
     */
    public function fetchAll()
    {
        $list = array();

        $sql = "SELECT alg.rowid, alg.fk_usergroup, alg.limit_enabled, alg.limit_days,";
        $sql .= " ug.nom as group_name";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." alg";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup ug ON ug.rowid = alg.fk_usergroup";
        $sql .= " ORDER BY ug.nom ASC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $list[$obj->fk_usergroup] = array(
                    'rowid' => $obj->rowid,
                    'fk_usergroup' => $obj->fk_usergroup,
                    'limit_enabled' => $obj->limit_enabled,
                    'limit_days' => $obj->limit_days,
                    'group_name' => $obj->group_name,
                );
            }
            $this->db->free($resql);
        }

        return $list;
    }
}
