<?php
/**
 * 
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
require_once('include/main/WebUI.php');

$log = &LoggerManager::getLogger('CurrencyUpdate');
$log->debug('Start CRON:' . __FILE__);

$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
$activeBankId = $moduleModel->getActiveBankId();
if (!empty($activeBankId)) {
	$yesterday = date('Y-m-d', strtotime('-1 day'));
	$lastWorkingDay = vtlib\Functions::getLastWorkingDay($yesterday);
	$status = $moduleModel->fetchCurrencyRates($lastWorkingDay, true);

	if ($status) {
		$log->info('Successfully fetched new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
	} else {
		$log->warn('Failed to fetch new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
	}
} else {
	$log->warn('Update of system currency rates ignored, no active bankin settings.');
}
$log->debug('End CRON:' . __FILE__);
