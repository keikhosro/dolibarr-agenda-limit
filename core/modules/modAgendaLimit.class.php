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
 * \defgroup   agendalimit     Module AgendaLimit
 * \brief      Module to limit how far ahead users can view in the agenda/calendar
 * \file       core/modules/modAgendaLimit.class.php
 * \ingroup    agendalimit
 * \brief      Description and activation file for module AgendaLimit
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module AgendaLimit
 */
class modAgendaLimit extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        // Module ID (must be unique)
        // Use https://wiki.dolibarr.org/index.php/List_of_modules_id to get a free number
        $this->numero = 500200; // Choose a unique number - changed to avoid conflict

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'agendalimit';

        // Family can be 'base' (core modules), 'crm', 'financial', 'hr', 'projects', 'products', 'ecm', 'technic' (for technical modules), 'interface' (for interface modules), 'other'
        $this->family = "projects";

        // Module position in the family on module setup page
        $this->module_position = '90';

        // Module label
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description
        $this->description = "Limit how far ahead users can view events in the Agenda/Calendar module";

        // Used only if file README.md and target URL is not set
        $this->descriptionlong = "This module allows administrators to restrict users from viewing agenda events beyond a configurable time period (e.g., 1 month, 3 months). The restriction can be applied per user or per user group.";

        // Author
        $this->editor_name = 'Your Name';
        $this->editor_url = '';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.0.0';

        // Url to the file with your last numberversion of this module
        //$this->url_last_version = '';

        // Key used in llx_const table to save module status enabled/disabled
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module
        $this->picto = 'calendar';

        // Define some features supported by module
        $this->module_parts = array(
            // Set this to 1 if module has its own css file
            'css' => array(),
            // JS is injected directly via hooks, not loaded as file
            'js' => array(),
            // Hook contexts - register for all agenda-related pages
            'hooks' => array(
                'main',
                'agendalist',
                'actioncard',
                'actioncomm',
                'comm',
                'globalcard',
            ),
            // Set this to 1 if features of module are opened to external users
            'moduleforexternal' => 0,
        );

        // Data directories to create when module is enabled
        $this->dirs = array();

        // Config pages - use unique filename to avoid conflicts with other modules
        $this->config_page_url = array("agendalimit_setup.php@agendalimit");

        // Dependencies
        $this->hidden = false;
        $this->depends = array('modAgenda');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array("agendalimit@agendalimit");

        // Constants
        $this->const = array(
            array('AGENDALIMIT_ENABLED', 'chaine', '0', 'Enable agenda lookahead limit globally', 0, 'current', 1),
            array('AGENDALIMIT_DEFAULT_DAYS', 'chaine', '30', 'Default number of days users can look ahead in agenda', 0, 'current', 1),
            array('AGENDALIMIT_USE_GROUPS', 'chaine', '1', 'Allow setting limits per user group', 0, 'current', 1),
        );

        // Array to add new pages in new tabs
        $this->tabs = array();

        // Dictionaries
        $this->dictionaries = array();

        // Boxes/Widgets
        $this->boxes = array();

        // Cronjobs (List of cron jobs entries to add when module is enabled)
        $this->cronjobs = array();

        // Permissions provided by this module
        $r = 0;

        // Permission to configure the module
        $this->rights[$r][0] = $this->numero + $r + 1;
        $this->rights[$r][1] = 'Configure agenda limit settings';
        $this->rights[$r][2] = 'a';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'configure';
        $r++;

        // Permission to bypass the limit
        $this->rights[$r][0] = $this->numero + $r + 1;
        $this->rights[$r][1] = 'Bypass agenda lookahead limit';
        $this->rights[$r][2] = 'r';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'bypass';
        $r++;

        // Main menu entries
        $this->menu = array();
        $r = 0;

        // Left menu entry under Agenda
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=agenda',
            'type' => 'left',
            'titre' => 'AgendaLimitSetup',
            'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth"'),
            'mainmenu' => 'agenda',
            'leftmenu' => 'agendalimit',
            'url' => '/agendalimit/admin/agendalimit_setup.php',
            'langs' => 'agendalimit@agendalimit',
            'position' => 1000 + $r,
            'enabled' => '$conf->agendalimit->enabled && $user->admin',
            'perms' => '$user->admin',
            'target' => '',
            'user' => 0,
        );

        // Exports
        $this->export_code = array();
        $this->export_label = array();
        $this->export_icon = array();
        $this->export_fields_array = array();
        $this->export_TypeFields_array = array();
        $this->export_entities_array = array();

        // Imports
        $this->import_code = array();
        $this->import_label = array();
        $this->import_icon = array();
        $this->import_entities_array = array();
        $this->import_tables_array = array();
        $this->import_tables_creator_array = array();
        $this->import_fields_array = array();
        $this->import_fieldshidden_array = array();
        $this->import_convertvalue_array = array();
        $this->import_regex_array = array();
        $this->import_examplevalues_array = array();
        $this->import_updatekeys_array = array();
        $this->import_run_sql_after_array = array();
    }

    /**
     * Function called when module is enabled.
     * The init function adds constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;

        $result = $this->_load_tables('/agendalimit/sql/');
        if ($result < 0) {
            return -1;
        }

        // Permissions
        $this->remove($options);

        $sql = array();

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}
