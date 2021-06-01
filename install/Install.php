<?php

chdir(__DIR__ . '/../');
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../');
$requiredVendors = [
	'vendor/smarty/smarty',
	'vendor/phpmailer/phpmailer',
	'vendor/ezyang/htmlpurifier',
	'vendor/simshaun/recurr',
];
foreach ($requiredVendors as $dir) {
	if (!file_exists($dir)) {
		echo "Directory not found: $dir. For more information, visit <a href=\"https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/category/installation-updates\" rel=\"noreferrer noopener\">https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/category/installation-updates</a>";
		return false;
	}
}
// Adjust error_reporting favourable to deployment.
$checkLibrary = true;
require_once 'include/main/WebUI.php';
\App\Language::$customDirectory = 'install';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'Install';
include_once 'include/RequirementsValidation.php';
require_once 'install/views/Index.php';
require_once 'install/models/Utils.php';
require_once 'install/models/InitSchema.php';

\App\Config::set('performance', 'recursiveTranslate', true);
App\Session::init();

if (isset($_SESSION['authenticated_user_id'])) {
	unset($_SESSION);
}

try {
	$request = App\Request::init();
	if (!$request->getMode() && \App\Config::main('application_unique_key')) {
		Install_Utils_Model::cleanConfiguration();
	}
	$install = new Install_Index_View();
	if (!$request->isAjax()) {
		$install->preProcess($request);
	}
	$install->process($request);
	if (!$request->isAjax()) {
		$install->postProcess($request);
	}
} catch (\Throwable $th) {
	echo '<pre>' . $th->__toString();
}
