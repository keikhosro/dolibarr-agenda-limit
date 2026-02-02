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
 * \file       agendalimit_check.php
 * \ingroup    agendalimit
 * \brief      Include file to check agenda date restrictions
 *
 * This file should be included at the beginning of agenda pages to enforce
 * the date lookahead restriction. It modifies the date parameters before
 * the page processes them.
 */

// Only run if we're already in Dolibarr context
if (!defined('DOL_VERSION')) {
    return;
}

dol_include_once('/agendalimit/class/agendalimit.class.php');

/**
 * Check and enforce agenda date limits
 *
 * @return void
 */
function agendalimit_enforce_date_limit()
{
    global $conf, $user, $langs, $db;

    // Skip if module or feature is disabled
    if (empty($conf->agendalimit->enabled) || empty($conf->global->AGENDALIMIT_ENABLED)) {
        return;
    }

    // Skip for admins
    if (!empty($user->admin)) {
        return;
    }

    // Get the user's max allowed date
    $maxDate = AgendaLimit::getMaxDateForUser($user);
    if ($maxDate === null) {
        return; // No limit for this user
    }

    // Check and enforce date parameters
    $enforced = false;

    // Year/month/day parameters (calendar navigation)
    $year = GETPOST('year', 'int');
    $month = GETPOST('month', 'int');
    $day = GETPOST('day', 'int');

    if ($year > 0) {
        $month = ($month > 0) ? $month : date('n');
        $day = ($day > 0) ? $day : 1;

        $requestedDate = dol_mktime(0, 0, 0, $month, $day, $year);

        if ($requestedDate > $maxDate) {
            // Enforce the limit
            $_GET['year'] = date('Y', $maxDate);
            $_GET['month'] = date('n', $maxDate);
            $_REQUEST['year'] = date('Y', $maxDate);
            $_REQUEST['month'] = date('n', $maxDate);

            $enforced = true;
        }
    }

    // Check week number if used
    $week = GETPOST('week', 'int');
    if ($week > 0 && $year > 0) {
        // Calculate the Monday of the requested week
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $weekStart = $dto->getTimestamp();

        if ($weekStart > $maxDate) {
            // Calculate max week
            $maxWeek = date('W', $maxDate);
            $maxYear = date('Y', $maxDate);

            $_GET['week'] = $maxWeek;
            $_GET['year'] = $maxYear;
            $_REQUEST['week'] = $maxWeek;
            $_REQUEST['year'] = $maxYear;

            $enforced = true;
        }
    }

    // Show warning if date was enforced
    if ($enforced) {
        $langs->load('agendalimit@agendalimit');
        setEventMessages($langs->trans('AgendaLimitExceeded', dol_print_date($maxDate, 'day')), null, 'warnings');
    }
}

// Execute the check
agendalimit_enforce_date_limit();
