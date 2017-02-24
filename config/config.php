<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

/**
 * The configuration file for FHS system
 * is located at /etc/vtigercrm directory.
 */

if (!function_exists('stacktrace')) {
    function stacktrace($stacktrace) {
        $out = str_repeat("=", 50) ."\n";
        $i = 1;
        foreach($stacktrace as $node) {
            $out .= "$i. ".($node['file']) .":" .$node['function'] ."(" .$node['line'].")\n";
            $i++;
        }
        return $out;
    } 
}

if (!function_exists('awlog')) {
    function awlog($myvar, $label='', $showtrace=false) {
        $bt = debug_backtrace();
        $caller = array_shift($bt);
	    error_log('AW ***** in '.$caller['file'].' on line '.$caller['line'].'*****************************************************');
        if ($showtrace) {
            foreach($bt as $trace) {
                error_log('AW -> '.$trace['file'].' on line '.$trace['line']);
            }
        }
	    error_log('AW '.$label.' '.var_export($myvar, true));
    }
}

require('config/config.inc.php');
if (file_exists('config/config_override.php')) {
	require 'config/config_override.php';
}
