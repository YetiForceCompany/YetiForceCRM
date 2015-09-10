<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
require_once('include/main/WebUI.php');

$log = &LoggerManager::getLogger('CurrencyUpdate');
$log->debug('Start CRON:' . __FILE__);

$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
$yesterday = date('Y-m-d', strtotime('-1 day'));
$lastWorkingDay = Vtiger_Functions::getLastWorkingDay($yesterday);

$status = $moduleModel->fetchCurrencyRates($lastWorkingDay, true);

if ($status) {
	$log->debug('Successfully fetched new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
} else {
	$log->debug('Failed to fetch new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
}

$log->debug('End CRON:' . __FILE__);
