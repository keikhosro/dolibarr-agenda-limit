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
 * \file       class/agendalimit.class.php
 * \ingroup    agendalimit
 * \brief      Class to manage agenda limits for users and groups
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class AgendaLimit
 * Manages agenda lookahead limits for users
 */
class AgendaLimit extends CommonObject
{
    /**
     * @var string ID of module
     */
    public $module = 'agendalimit';

    /**
     * @var string Element type
     */
    public $element = 'agendalimit';

    /**
     * @var string Name of table without prefix
     */
    public $table_element = 'agendalimit_user';

    /**
     * @var int User ID
     */
    public $fk_user;

    /**
     * @var int Whether limit is enabled (1) or disabled (0)
     */
    public $limit_enabled;

    /**
     * @var int Number of days user can look ahead
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
        if (isset($this->fk_user)) {
            $this->fk_user = (int) $this->fk_user;
        }
        if (isset($this->limit_enabled)) {
            $this->limit_enabled = (int) $this->limit_enabled;
        }
        if (isset($this->limit_days)) {
            $this->limit_days = (int) $this->limit_days;
        }

        // Check parameters
        if (empty($this->fk_user)) {
            $this->error = 'ErrorUserRequired';
            return -1;
        }

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        $sql .= "fk_user,";
        $sql .= "limit_enabled,";
        $sql .= "limit_days,";
        $sql .= "date_creation,";
        $sql .= "fk_user_creat";
        $sql .= ") VALUES (";
        $sql .= ((int) $this->fk_user).",";
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

            if (!$notrigger) {
                // Triggers are not needed for this module
            }
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
     * @param int    $id   Id of user limit record
     * @param int    $fk_user User ID to load limit for
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id = 0, $fk_user = 0)
    {
        $sql = "SELECT rowid, fk_user, limit_enabled, limit_days, date_creation, fk_user_creat, fk_user_modif";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        if ($id > 0) {
            $sql .= " WHERE rowid = ".((int) $id);
        } elseif ($fk_user > 0) {
            $sql .= " WHERE fk_user = ".((int) $fk_user);
        } else {
            return -1;
        }

        dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;
                $this->fk_user = $obj->fk_user;
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
     * Get the maximum date a user can view in the agenda
     *
     * @param User $user User to check
     * @return int|null Unix timestamp of max date, or null if no limit
     */
    public static function getMaxDateForUser(User $user)
    {
        global $db, $conf;

        // Check if module is enabled
        if (empty($conf->agendalimit->enabled)) {
            return null;
        }

        // Check global enable setting
        if (empty($conf->global->AGENDALIMIT_ENABLED)) {
            return null;
        }

        // Check if user has bypass permission
        if (!empty($user->rights->agendalimit->bypass)) {
            return null;
        }

        // Admins bypass the limit by default
        if (!empty($user->admin)) {
            return null;
        }

        $limitDays = null;

        // First check user-specific limit
        $sql = "SELECT limit_enabled, limit_days FROM ".MAIN_DB_PREFIX."agendalimit_user";
        $sql .= " WHERE fk_user = ".((int) $user->id);

        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);
            if ($obj->limit_enabled) {
                $limitDays = (int) $obj->limit_days;
            } else {
                // User has explicit "no limit" setting
                return null;
            }
        }

        // If no user-specific setting, check group settings
        if ($limitDays === null && !empty($conf->global->AGENDALIMIT_USE_GROUPS)) {
            $sql = "SELECT MIN(g.limit_days) as min_days";
            $sql .= " FROM ".MAIN_DB_PREFIX."agendalimit_group g";
            $sql .= " JOIN ".MAIN_DB_PREFIX."usergroup_user ugu ON ugu.fk_usergroup = g.fk_usergroup";
            $sql .= " WHERE ugu.fk_user = ".((int) $user->id);
            $sql .= " AND g.limit_enabled = 1";

            $resql = $db->query($sql);
            if ($resql && $db->num_rows($resql) > 0) {
                $obj = $db->fetch_object($resql);
                if ($obj->min_days !== null) {
                    $limitDays = (int) $obj->min_days;
                }
            }
        }

        // If still no limit found, use default
        if ($limitDays === null) {
            if (!empty($conf->global->AGENDALIMIT_DEFAULT_DAYS)) {
                $limitDays = (int) $conf->global->AGENDALIMIT_DEFAULT_DAYS;
            } else {
                return null;
            }
        }

        // Calculate the maximum allowed date
        $maxDate = dol_now() + ($limitDays * 24 * 60 * 60);

        return $maxDate;
    }

    /**
     * Check if a specific date is allowed for a user
     *
     * @param User $user User to check
     * @param int $timestamp Unix timestamp to check
     * @return bool True if allowed, false if not
     */
    public static function isDateAllowed(User $user, $timestamp)
    {
        $maxDate = self::getMaxDateForUser($user);

        if ($maxDate === null) {
            return true; // No limit
        }

        return ($timestamp <= $maxDate);
    }

    /**
     * Get all user limits
     *
     * @return array Array of user limit records
     */
    public function fetchAll()
    {
        $list = array();

        $sql = "SELECT al.rowid, al.fk_user, al.limit_enabled, al.limit_days,";
        $sql .= " u.login, u.firstname, u.lastname";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." al";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = al.fk_user";
        $sql .= " ORDER BY u.login ASC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $list[$obj->fk_user] = array(
                    'rowid' => $obj->rowid,
                    'fk_user' => $obj->fk_user,
                    'limit_enabled' => $obj->limit_enabled,
                    'limit_days' => $obj->limit_days,
                    'login' => $obj->login,
                    'firstname' => $obj->firstname,
                    'lastname' => $obj->lastname,
                );
            }
            $this->db->free($resql);
        }

        return $list;
    }
}
