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
 * \file       admin/setup.php
 * \ingroup    agendalimit
 * \brief      Setup page for Agenda Limit module
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

dol_include_once('/agendalimit/class/agendalimit.class.php');
dol_include_once('/agendalimit/class/agendalimitgroup.class.php');
dol_include_once('/agendalimit/lib/agendalimit.lib.php');

// Load translations
$langs->loadLangs(array("admin", "agendalimit@agendalimit"));

// Security check
if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$tab = GETPOST('tab', 'aZ09');
if (empty($tab)) {
    $tab = 'settings';
}

/*
 * Actions
 */

if ($action == 'update') {
    $error = 0;

    // Update global settings
    if (GETPOST('AGENDALIMIT_ENABLED') !== null) {
        $res = dolibarr_set_const($db, "AGENDALIMIT_ENABLED", GETPOST('AGENDALIMIT_ENABLED', 'int'), 'chaine', 0, '', $conf->entity);
        if (!$res > 0) $error++;
    }
    if (GETPOST('AGENDALIMIT_DEFAULT_DAYS') !== null) {
        $res = dolibarr_set_const($db, "AGENDALIMIT_DEFAULT_DAYS", GETPOST('AGENDALIMIT_DEFAULT_DAYS', 'int'), 'chaine', 0, '', $conf->entity);
        if (!$res > 0) $error++;
    }
    if (GETPOST('AGENDALIMIT_USE_GROUPS') !== null) {
        $res = dolibarr_set_const($db, "AGENDALIMIT_USE_GROUPS", GETPOST('AGENDALIMIT_USE_GROUPS', 'int'), 'chaine', 0, '', $conf->entity);
        if (!$res > 0) $error++;
    }

    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

// Save user limit
if ($action == 'saveuserlimit') {
    $fk_user = GETPOST('fk_user', 'int');
    $limit_enabled = GETPOST('limit_enabled_'.$fk_user, 'int');
    $limit_days = GETPOST('limit_days_'.$fk_user, 'int');

    if ($fk_user > 0) {
        $agendalimit = new AgendaLimit($db);
        $result = $agendalimit->fetch(0, $fk_user);

        if ($result > 0) {
            // Update existing
            $agendalimit->limit_enabled = $limit_enabled;
            $agendalimit->limit_days = $limit_days;
            $agendalimit->update($user);
        } else {
            // Create new
            $agendalimit->fk_user = $fk_user;
            $agendalimit->limit_enabled = $limit_enabled;
            $agendalimit->limit_days = $limit_days;
            $agendalimit->create($user);
        }

        setEventMessages($langs->trans("UserLimitSaved"), null, 'mesgs');
    }
}

// Delete user limit
if ($action == 'deleteuserlimit') {
    $fk_user = GETPOST('fk_user', 'int');

    if ($fk_user > 0) {
        $agendalimit = new AgendaLimit($db);
        $result = $agendalimit->fetch(0, $fk_user);
        if ($result > 0) {
            $agendalimit->delete($user);
            setEventMessages($langs->trans("UserLimitDeleted"), null, 'mesgs');
        }
    }
}

// Save group limit
if ($action == 'savegrouplimit') {
    $fk_usergroup = GETPOST('fk_usergroup', 'int');
    $limit_enabled = GETPOST('limit_enabled_g'.$fk_usergroup, 'int');
    $limit_days = GETPOST('limit_days_g'.$fk_usergroup, 'int');

    if ($fk_usergroup > 0) {
        $agendalimitgroup = new AgendaLimitGroup($db);
        $result = $agendalimitgroup->fetch(0, $fk_usergroup);

        if ($result > 0) {
            // Update existing
            $agendalimitgroup->limit_enabled = $limit_enabled;
            $agendalimitgroup->limit_days = $limit_days;
            $agendalimitgroup->update($user);
        } else {
            // Create new
            $agendalimitgroup->fk_usergroup = $fk_usergroup;
            $agendalimitgroup->limit_enabled = $limit_enabled;
            $agendalimitgroup->limit_days = $limit_days;
            $agendalimitgroup->create($user);
        }

        setEventMessages($langs->trans("GroupLimitSaved"), null, 'mesgs');
    }
}

// Delete group limit
if ($action == 'deletegrouplimit') {
    $fk_usergroup = GETPOST('fk_usergroup', 'int');

    if ($fk_usergroup > 0) {
        $agendalimitgroup = new AgendaLimitGroup($db);
        $result = $agendalimitgroup->fetch(0, $fk_usergroup);
        if ($result > 0) {
            $agendalimitgroup->delete($user);
            setEventMessages($langs->trans("GroupLimitDeleted"), null, 'mesgs');
        }
    }
}

/*
 * View
 */

$form = new Form($db);

$page_name = "AgendaLimitSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = agendalimitAdminPrepareHead();
print dol_get_fiche_head($head, $tab, $langs->trans($page_name), -1, 'calendar');

/*
 * Settings Tab
 */
if ($tab == 'settings') {
    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=settings">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="update">';

    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans("Parameter").'</td>';
    print '<td>'.$langs->trans("Value").'</td>';
    print '</tr>';

    // Enable/disable module functionality
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("AgendaLimitEnabled").'</td>';
    print '<td>';
    print $form->selectyesno("AGENDALIMIT_ENABLED", $conf->global->AGENDALIMIT_ENABLED, 1);
    print '</td>';
    print '</tr>';

    // Default limit in days
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("AgendaLimitDefaultDays").'</td>';
    print '<td>';
    print '<input type="number" name="AGENDALIMIT_DEFAULT_DAYS" value="'.($conf->global->AGENDALIMIT_DEFAULT_DAYS ? $conf->global->AGENDALIMIT_DEFAULT_DAYS : 30).'" min="1" max="3650" class="flat width100">';
    print ' '.$langs->trans("Days");
    print '</td>';
    print '</tr>';

    // Use group settings
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("AgendaLimitUseGroups").'</td>';
    print '<td>';
    print $form->selectyesno("AGENDALIMIT_USE_GROUPS", $conf->global->AGENDALIMIT_USE_GROUPS, 1);
    print '</td>';
    print '</tr>';

    print '</table>';

    print '<br>';
    print '<div class="center">';
    print '<input class="button button-save" type="submit" value="'.$langs->trans("Save").'">';
    print '</div>';

    print '</form>';

    // Info box
    print '<br>';
    print '<div class="info">';
    print $langs->trans("AgendaLimitInfo");
    print '</div>';
}

/*
 * User Limits Tab
 */
if ($tab == 'users') {
    // Get all users
    $sql = "SELECT u.rowid, u.login, u.firstname, u.lastname, u.statut as status,";
    $sql .= " al.rowid as limit_id, al.limit_enabled, al.limit_days";
    $sql .= " FROM ".MAIN_DB_PREFIX."user u";
    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."agendalimit_user al ON al.fk_user = u.rowid";
    $sql .= " WHERE u.statut = 1"; // Only active users
    $sql .= " ORDER BY u.login ASC";

    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);

        print '<table class="noborder centpercent">';
        print '<tr class="liste_titre">';
        print '<td>'.$langs->trans("User").'</td>';
        print '<td class="center">'.$langs->trans("LimitEnabled").'</td>';
        print '<td class="center">'.$langs->trans("LimitDays").'</td>';
        print '<td class="center">'.$langs->trans("Action").'</td>';
        print '</tr>';

        if ($num > 0) {
            while ($obj = $db->fetch_object($resql)) {
                $userStatic = new User($db);
                $userStatic->id = $obj->rowid;
                $userStatic->login = $obj->login;
                $userStatic->firstname = $obj->firstname;
                $userStatic->lastname = $obj->lastname;

                print '<tr class="oddeven">';
                print '<td>'.$userStatic->getNomUrl(1).' - '.$userStatic->getFullName($langs).'</td>';

                print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=users">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="saveuserlimit">';
                print '<input type="hidden" name="fk_user" value="'.$obj->rowid.'">';

                print '<td class="center">';
                print $form->selectyesno("limit_enabled_".$obj->rowid, ($obj->limit_enabled !== null ? $obj->limit_enabled : 1), 1);
                print '</td>';

                print '<td class="center">';
                $limitDays = ($obj->limit_days !== null) ? $obj->limit_days : $conf->global->AGENDALIMIT_DEFAULT_DAYS;
                print '<input type="number" name="limit_days_'.$obj->rowid.'" value="'.$limitDays.'" min="1" max="3650" class="flat width75">';
                print '</td>';

                print '<td class="center">';
                print '<input type="submit" class="button button-save smallpaddingimp" value="'.$langs->trans("Save").'">';
                print '</form>';

                if ($obj->limit_id > 0) {
                    print ' <a href="'.$_SERVER["PHP_SELF"].'?action=deleteuserlimit&fk_user='.$obj->rowid.'&tab=users&token='.newToken().'" class="button button-delete smallpaddingimp" onclick="return confirm(\''.$langs->trans("ConfirmDelete").'\');">'.$langs->trans("Delete").'</a>';
                }
                print '</td>';
                print '</tr>';
            }
        } else {
            print '<tr class="oddeven"><td colspan="4" class="center">'.$langs->trans("NoUsers").'</td></tr>';
        }

        print '</table>';
    }

    // Info about user limits
    print '<br>';
    print '<div class="info">';
    print $langs->trans("AgendaLimitUserInfo");
    print '</div>';
}

/*
 * Group Limits Tab
 */
if ($tab == 'groups') {
    // Check if groups are enabled
    if (empty($conf->global->AGENDALIMIT_USE_GROUPS)) {
        print '<div class="warning">'.$langs->trans("GroupLimitsDisabled").'</div>';
    } else {
        // Get all user groups
        $sql = "SELECT ug.rowid, ug.nom as name,";
        $sql .= " alg.rowid as limit_id, alg.limit_enabled, alg.limit_days";
        $sql .= " FROM ".MAIN_DB_PREFIX."usergroup ug";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."agendalimit_group alg ON alg.fk_usergroup = ug.rowid";
        $sql .= " ORDER BY ug.nom ASC";

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);

            print '<table class="noborder centpercent">';
            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("Group").'</td>';
            print '<td class="center">'.$langs->trans("LimitEnabled").'</td>';
            print '<td class="center">'.$langs->trans("LimitDays").'</td>';
            print '<td class="center">'.$langs->trans("Action").'</td>';
            print '</tr>';

            if ($num > 0) {
                while ($obj = $db->fetch_object($resql)) {
                    print '<tr class="oddeven">';
                    print '<td>'.$obj->name.'</td>';

                    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=groups">';
                    print '<input type="hidden" name="token" value="'.newToken().'">';
                    print '<input type="hidden" name="action" value="savegrouplimit">';
                    print '<input type="hidden" name="fk_usergroup" value="'.$obj->rowid.'">';

                    print '<td class="center">';
                    print $form->selectyesno("limit_enabled_g".$obj->rowid, ($obj->limit_enabled !== null ? $obj->limit_enabled : 1), 1);
                    print '</td>';

                    print '<td class="center">';
                    $limitDays = ($obj->limit_days !== null) ? $obj->limit_days : $conf->global->AGENDALIMIT_DEFAULT_DAYS;
                    print '<input type="number" name="limit_days_g'.$obj->rowid.'" value="'.$limitDays.'" min="1" max="3650" class="flat width75">';
                    print '</td>';

                    print '<td class="center">';
                    print '<input type="submit" class="button button-save smallpaddingimp" value="'.$langs->trans("Save").'">';
                    print '</form>';

                    if ($obj->limit_id > 0) {
                        print ' <a href="'.$_SERVER["PHP_SELF"].'?action=deletegrouplimit&fk_usergroup='.$obj->rowid.'&tab=groups&token='.newToken().'" class="button button-delete smallpaddingimp" onclick="return confirm(\''.$langs->trans("ConfirmDelete").'\');">'.$langs->trans("Delete").'</a>';
                    }
                    print '</td>';
                    print '</tr>';
                }
            } else {
                print '<tr class="oddeven"><td colspan="4" class="center">'.$langs->trans("NoGroups").'</td></tr>';
            }

            print '</table>';
        }
    }

    // Info about group limits
    print '<br>';
    print '<div class="info">';
    print $langs->trans("AgendaLimitGroupInfo");
    print '</div>';
}

print dol_get_fiche_end();

llxFooter();
$db->close();
