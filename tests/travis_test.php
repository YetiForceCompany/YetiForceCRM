<?php
/**
 * Travis CI test script
 * @package YetiForce.Travis CI
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');

try {
	$startTime = microtime(true);
	define('REQUEST_MODE', 'WebUI');
	define('ROOT_DIRECTORY', getcwd());

	require 'include/main/WebUI.php';

	ob_start();
	$webUI = new Vtiger_WebUI();
	$webUI->process(AppRequest::init());
	ob_end_clean();

	$user = CRMEntity::getInstance('Users');
	$user->column_fields['user_name'] = 'admin';
	if ($user->doLogin('admin')) {
		Vtiger_Session::set('AUTHUSERID', $userid);
		Vtiger_Session::set('authenticated_user_id', $userid);
		Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
		Vtiger_Session::set('authenticated_user_language', AppConfig::main('default_language'));
		Vtiger_Session::set('user_name', $username);
		Vtiger_Session::set('full_user_name', vtlib\Functions::getUserRecordLabel($userid));
	}

	echo 'Creating a user' . PHP_EOL;

	$user = Vtiger_Record_Model::getCleanInstance('Users');
	$user->set('user_name', 'demo1');
	$user->set('email1', 'd1emo@yetiforce.com');
	$user->set('first_name', 'Demo');
	$user->set('last_name', 'YetiForce');
	$user->set('user_password', 'demo');
	$user->set('confirm_password', 'demo');
	$user->set('roleid', 'H2');
	$user->save();

	echo 'Generating test data' . PHP_EOL;

	$rekord = Vtiger_Record_Model::getCleanInstance('Accounts');
	$rekord->set('accountname', 'YetiForce Sp. z o.o.');
	$rekord->set('assigned_user_id', $user->getId());
	$rekord->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
	$rekord->save();
	$rekord->isEditable();
	$rekord->isWatchingRecord();
	$rekord->set('accounttype', 'Customer');
	$rekord->set('mode', 'edit');
	$rekord->save();
	$rekord->delete();

	$_SERVER['HTTP_X_REQUESTED_WITH'] = true;


	ob_start();
	$testModule = 'TestModule.zip';
	file_put_contents($testModule, file_get_contents('https://tests.yetiforce.com/' . $_SERVER['YETI_KEY']));
	ob_end_clean();
	if (file_exists($testModule)) {
		$package = new vtlib\Package();
		$package->import($testModule);
	} else {
		throw new Exception('No file');
	}
	echo 'Start cron 1' . PHP_EOL;
	$cronTasks = vtlib\Cron::listAllActiveInstances();
	foreach ($cronTasks as $cronTask) {
		$cronTask->markRunning();
		checkFileAccess($cronTask->getHandlerFile());
		require_once $cronTask->getHandlerFile();
		$cronTask->markFinished();
	}
	echo 'Start cron 2' . PHP_EOL;
	require 'cron/vtigercron.php';

	echo 'Checking language files' . PHP_EOL;
	$templatepath = 'languages/';
	$flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templatepath, $flags), RecursiveIteratorIterator::SELF_FIRST);
	foreach ($objects as $name => $object) {
		if (!is_dir($name)) {
			include_once $name;
		}
	}
} catch (\Exception $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\AppException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\NoPermittedException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\NoPermittedToRecordException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\NoPermittedForAdminException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\CsrfException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\APINoPermittedException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
} catch (\PDOException $e) {
	echo PHP_EOL . 'INSTALLATION FAILED! ' . $e->getMessage() . PHP_EOL . $e->xdebug_message . PHP_EOL . $e->getTrace() . PHP_EOL . $e->__toString() . PHP_EOL . $e->getTraceAsString();
}
