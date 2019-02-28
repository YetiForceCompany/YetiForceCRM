<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

require_once 'include/ConfigUtils.php';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'WebUI';

$webUI = new \App\WebUI();
$webUI->process();
require ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'index.php';
