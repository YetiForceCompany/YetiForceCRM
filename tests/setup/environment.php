<?php
/**
 * Travis CI test script.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__ . '/../../');
set_include_path(getcwd());
define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}
$log = '';
$log .= 'locale: ' . setlocale(LC_ALL, 0) . PHP_EOL . PHP_EOL;
$log .= '$_SERVER:' . print_r($_SERVER, true) . PHP_EOL;
$error = $ok = $info = $files = [];
foreach (Settings_ConfReport_Module_Model::getStabilityConf() as $key => $value) {
	if (empty($value['incorrect'])) {
		$ok[$key] = $value;
	} else {
		$error[$key] = $value;
	}
}
foreach (Settings_ConfReport_Module_Model::getSecurityConf() as $key => $value) {
	if (empty($value['status'])) {
		$ok[$key] = $value;
	} else {
		$error[$key] = $value;
	}
}
foreach (Settings_ConfReport_Module_Model::getSystemInfo() as $key => $value) {
	$info[$key] = print_r($value, true);
}
foreach (Settings_ConfReport_Module_Model::getPermissionsFiles() as $key => $value) {
	$files[$key] = $value;
}
foreach (Settings_ConfReport_Module_Model::getDbConf() as $key => $value) {
	if (empty($value['status'])) {
		$ok[$key] = $value;
	} else {
		$error[$key] = $value;
	}
}
foreach (\Settings_ConfReport_Module_Model::getLibrary() as $key => $value) {
	if ($value['status'] === 'LBL_NO') {
		$libs[$key] = $value['name'];
	}
}
$log .= 'ConfReport:' . print_r(['errors' => $error, 'ok' => $ok, 'files' => $files, 'libs' => $libs], true) . PHP_EOL . PHP_EOL;
$log .= 'ini_get_all:' . print_r(ini_get_all(), true) . PHP_EOL . PHP_EOL;
file_put_contents('cache/logs/environment.log', $log);
