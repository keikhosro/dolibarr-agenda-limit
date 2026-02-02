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
 * \file       lib/agendalimit.lib.php
 * \ingroup    agendalimit
 * \brief      Library files with common functions for AgendaLimit
 */

/**
 * Prepare admin pages header for AgendaLimit module
 *
 * @return array Array of tabs
 */
function agendalimitAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("agendalimit@agendalimit");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/agendalimit/admin/agendalimit_setup.php", 1).'?tab=settings';
    $head[$h][1] = $langs->trans("Settings");
    $head[$h][2] = 'settings';
    $h++;

    $head[$h][0] = dol_buildpath("/agendalimit/admin/agendalimit_setup.php", 1).'?tab=users';
    $head[$h][1] = $langs->trans("UserLimits");
    $head[$h][2] = 'users';
    $h++;

    $head[$h][0] = dol_buildpath("/agendalimit/admin/agendalimit_setup.php", 1).'?tab=groups';
    $head[$h][1] = $langs->trans("GroupLimits");
    $head[$h][2] = 'groups';
    $h++;

    complete_head_from_modules($conf, $langs, null, $head, $h, 'agendalimit@agendalimit');

    complete_head_from_modules($conf, $langs, null, $head, $h, 'agendalimit@agendalimit', 'remove');

    return $head;
}
