<?php
/* Copyright (C) 2025 Your Name <your@email.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       class/actions_agendalimit.class.php
 * \ingroup    agendalimit
 * \brief      Hook actions for agenda limit module
 */

/**
 * Class ActionsAgendaLimit
 */
class ActionsAgendaLimit
{
    /** @var DoliDB */
    public $db;
    /** @var string */
    public $error = '';
    /** @var array */
    public $errors = array();
    /** @var array */
    public $results = array();
    /** @var string */
    public $resprints = '';

    /**
     * Constructor
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get max date for current user
     * @return int|null Max timestamp or null
     */
    private function getMaxDateForCurrentUser()
    {
        global $conf, $user;

        if (empty($conf->agendalimit) || empty($conf->agendalimit->enabled)) {
            return null;
        }
        
        if (empty($conf->global->AGENDALIMIT_ENABLED)) {
            return null;
        }

        if (!empty($user->admin)) {
            return null;
        }

        if (!empty($user->rights->agendalimit->bypass)) {
            return null;
        }

        dol_include_once('/agendalimit/class/agendalimit.class.php');
        
        if (class_exists('AgendaLimit')) {
            return AgendaLimit::getMaxDateForUser($user);
        }
        
        return null;
    }

    /**
     * Check if on agenda page
     * @return bool
     */
    private function isAgendaPage()
    {
        $script = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
        return (strpos($script, '/comm/action/') !== false);
    }

    /**
     * Generate the JavaScript code for enforcement
     * @param int $maxDate Unix timestamp
     * @return string JavaScript code
     */
    private function getEnforcementJS($maxDate)
    {
        $maxDateStr = date('Y-m-d', $maxDate);
        $maxYear = date('Y', $maxDate);
        $maxMonth = date('n', $maxDate);
        $maxDay = date('j', $maxDate);
        
        return '
<script type="text/javascript">
(function() {
    "use strict";
    
    var maxDate = new Date("' . $maxDateStr . 'T23:59:59");
    var maxYear = ' . $maxYear . ';
    var maxMonth = ' . $maxMonth . ';
    var maxDay = ' . $maxDay . ';
    
    console.log("AgendaLimit: Max date is " + maxDate.toLocaleDateString());
    
    function getDateFromUrl(url) {
        try {
            var urlObj = new URL(url, window.location.origin);
            var year = parseInt(urlObj.searchParams.get("year")) || new Date().getFullYear();
            var month = parseInt(urlObj.searchParams.get("month")) || (new Date().getMonth() + 1);
            var day = parseInt(urlObj.searchParams.get("day")) || 1;
            return new Date(year, month - 1, day);
        } catch (e) {
            return null;
        }
    }
    
    function checkAndRedirect() {
        var currentDate = getDateFromUrl(window.location.href);
        if (currentDate && currentDate > maxDate) {
            console.log("AgendaLimit: Current date " + currentDate.toLocaleDateString() + " exceeds limit");
            var url = new URL(window.location.href);
            url.searchParams.set("year", maxYear);
            url.searchParams.set("month", maxMonth);
            if (url.searchParams.has("day")) {
                url.searchParams.set("day", maxDay);
            }
            alert("You cannot view agenda events beyond " + maxDate.toLocaleDateString() + ". Redirecting to the maximum allowed date.");
            window.location.href = url.toString();
            return true;
        }
        return false;
    }
    
    // Check immediately on page load
    if (!checkAndRedirect()) {
        console.log("AgendaLimit: Current view is within limits");
    }
    
    // Intercept all link clicks
    document.addEventListener("click", function(e) {
        var link = e.target.closest("a");
        if (!link || !link.href) return;
        
        // Only check agenda links
        if (link.href.indexOf("/comm/action/") === -1) return;
        if (link.href.indexOf("year=") === -1 && link.href.indexOf("month=") === -1) return;
        
        var targetDate = getDateFromUrl(link.href);
        if (targetDate && targetDate > maxDate) {
            e.preventDefault();
            e.stopPropagation();
            alert("You cannot view agenda events beyond " + maxDate.toLocaleDateString());
            console.log("AgendaLimit: Blocked navigation to " + targetDate.toLocaleDateString());
            return false;
        }
    }, true);
    
    // Intercept form submissions (for date picker)
    document.addEventListener("submit", function(e) {
        var form = e.target;
        var yearInput = form.querySelector("[name*=year]");
        var monthInput = form.querySelector("[name*=month]");
        
        if (yearInput && monthInput) {
            var year = parseInt(yearInput.value) || new Date().getFullYear();
            var month = parseInt(monthInput.value) || (new Date().getMonth() + 1);
            var testDate = new Date(year, month - 1, 1);
            
            if (testDate > maxDate) {
                e.preventDefault();
                alert("You cannot view agenda events beyond " + maxDate.toLocaleDateString());
                return false;
            }
        }
    }, true);
    
})();
</script>';
    }

    /**
     * Hook: addMoreHead - adds content to HTML head
     */
    public function addMoreHead($parameters, &$object, &$action, $hookmanager)
    {
        if (!$this->isAgendaPage()) {
            return 0;
        }

        $maxDate = $this->getMaxDateForCurrentUser();
        if ($maxDate !== null) {
            $this->resprints = $this->getEnforcementJS($maxDate);
        }

        return 0;
    }

    /**
     * Hook: doActions - server-side enforcement (backup)
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $langs;

        if (!$this->isAgendaPage()) {
            return 0;
        }

        $maxDate = $this->getMaxDateForCurrentUser();
        if ($maxDate === null) {
            return 0;
        }

        $year = GETPOST('year', 'int');
        $month = GETPOST('month', 'int');
        $day = GETPOST('day', 'int');

        if (empty($year)) $year = date('Y');
        if (empty($month)) $month = date('n');
        if (empty($day)) $day = 1;

        $requestedDate = dol_mktime(0, 0, 0, $month, $day, $year);

        if ($requestedDate > $maxDate) {
            $langs->load('agendalimit@agendalimit');
            setEventMessages($langs->trans('AgendaLimitExceeded', dol_print_date($maxDate, 'day')), null, 'warnings');

            $newParams = $_GET;
            $newParams['year'] = date('Y', $maxDate);
            $newParams['month'] = date('n', $maxDate);
            if (isset($newParams['day'])) {
                $newParams['day'] = date('j', $maxDate);
            }

            header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($newParams));
            exit();
        }

        return 0;
    }

    /**
     * Hook: printPageHeader - inject JS after page header
     */
    public function printPageHeader($parameters, &$object, &$action, $hookmanager)
    {
        return $this->addMoreHead($parameters, $object, $action, $hookmanager);
    }

    /**
     * Hook: formObjectOptions - show warning and inject JS
     */
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $langs;

        if (!$this->isAgendaPage()) {
            return 0;
        }

        $maxDate = $this->getMaxDateForCurrentUser();
        if ($maxDate !== null) {
            $langs->load('agendalimit@agendalimit');
            
            // Add warning message + JavaScript
            $this->resprints = '<div class="warning" style="margin: 10px 0; padding: 10px;">' . 
                img_warning() . ' ' . 
                $langs->trans('AgendaLimitActiveUntil', dol_print_date($maxDate, 'day')) . 
                '</div>';
            
            // Also inject JavaScript here as a fallback
            $this->resprints .= $this->getEnforcementJS($maxDate);
        }

        return 0;
    }

    /**
     * Hook: formConfirm - another hook point to inject JS
     */
    public function formConfirm($parameters, &$object, &$action, $hookmanager)
    {
        return $this->formObjectOptions($parameters, $object, $action, $hookmanager);
    }

    /**
     * Hook: printFieldListHeader - inject at list header
     */
    public function printFieldListHeader($parameters, &$object, &$action, $hookmanager)
    {
        return $this->formObjectOptions($parameters, $object, $action, $hookmanager);
    }

    /**
     * Hook: completeTabsHead - for tab pages
     */
    public function completeTabsHead($parameters, &$object, &$action, $hookmanager)
    {
        return $this->addMoreHead($parameters, $object, $action, $hookmanager);
    }
}
