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
 * \file       core/triggers/interface_99_modAgendaLimit_AgendaLimitTriggers.class.php
 * \ingroup    agendalimit
 * \brief      Triggers for AgendaLimit module
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolobarrtriggers.class.php';

/**
 * Class InterfaceAgendaLimitTriggers
 *
 * Trigger class for AgendaLimit events
 */
class InterfaceAgendaLimitTriggers extends DolibarrTriggers
{
    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "agenda";
        $this->description = "Triggers of the AgendaLimit module";
        $this->version = '1.0.0';
        $this->picto = 'calendar';
    }

    /**
     * Function called when a Dolibarr business event is done.
     *
     * @param string    $action     Event action code
     * @param Object    $object     Object concerned
     * @param User      $user       User object
     * @param Translate $langs      Language object
     * @param conf      $conf       Configuration object
     * @return int                  <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        // Note: Most enforcement is done via hooks, but this trigger can be used
        // for additional business event handling if needed in the future.

        if (empty($conf->agendalimit->enabled)) {
            return 0;
        }

        // Action viewing is not a business event trigger, so enforcement
        // is primarily handled in hooks. This trigger is reserved for
        // future use cases like logging or notifications.

        return 0;
    }
}
